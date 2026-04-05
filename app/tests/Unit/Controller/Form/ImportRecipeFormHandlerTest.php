<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Form;

use App\Controller\Form\ImportRecipeFormHandler;
use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use App\Transformer\RecipeSearchToEntity;
use Exception;
use Mockery;
use RuntimeException;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ImportRecipeFormHandlerTest extends TestCase
{
    private HttpClientInterface&MockInterface $client;
    private RecipeSearchToEntity&MockInterface $transformer;
    private RecipeRepository&MockInterface $recipeRepository;
    private string $apiUrlFormat = 'https://example.com/search?slug=%s&views=test';

    protected function setUp(): void
    {
        $this->client = Mockery::mock(HttpClientInterface::class);
        $this->transformer = Mockery::mock(RecipeSearchToEntity::class);
        $this->recipeRepository = Mockery::mock(RecipeRepository::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testHandleCallsCorrectUrlAndSavesRecipe(): void
    {
        $recipe = new Recipe();
        $memberData = ['title' => 'Test Recipe'];

        /** @var ResponseInterface&MockInterface $response */
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('toArray')
            ->andReturn(['hydra:member' => [$memberData]]);

        $this->client->shouldReceive('request')
            ->with('GET', 'https://example.com/search?slug=test-slug&views=test')
            ->once()
            ->andReturn($response);

        $this->transformer->shouldReceive('transform')
            ->with($memberData)
            ->once()
            ->andReturn($recipe);

        $this->recipeRepository->shouldReceive('save')
            ->with($recipe)
            ->once();

        $handler = $this->makeHandler();
        $handler->handle('test-slug');

        // Mockery expectations verified in tearDown via Mockery::close()
        $this->addToAssertionCount(1);
    }

    public function testHandleThrowsExceptionWhenTransformFails(): void
    {
        /** @var ResponseInterface&MockInterface $response */
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('toArray')
            ->andReturn(['hydra:member' => [['title' => 'Bad Data']]]);

        $this->client->shouldReceive('request')
            ->andReturn($response);

        $this->transformer->shouldReceive('transform')
            ->andThrow(new RuntimeException('Unexpected structure'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Recipe not found or invalid data structure.');

        $handler = $this->makeHandler();
        $handler->handle('bad-slug');
    }

    private function makeHandler(): ImportRecipeFormHandler
    {
        return new ImportRecipeFormHandler(
            $this->client,
            $this->transformer,
            $this->recipeRepository,
            $this->apiUrlFormat,
        );
    }
}
