<?php

declare(strict_types=1);

namespace App\Tests\Unit\Transformer;

use App\Entity\IngredientName;
use App\Repository\IngredientNameRepository;
use App\Transformer\RecipeSearchToEntity;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class RecipeSearchToEntityTest extends TestCase
{
    private IngredientNameRepository&MockInterface $repo;
    private RecipeSearchToEntity $transformer;

    protected function setUp(): void
    {
        $this->repo = Mockery::mock(IngredientNameRepository::class);
        $this->transformer = new RecipeSearchToEntity($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testTransformSetsRecipeNameAndSlug(): void
    {
        $this->repo->shouldReceive('findOrCreate')->andReturn(new IngredientName());

        $recipe = $this->transformer->transform($this->sampleData());

        $this->assertSame('Chocolate Cake', $recipe->getName());
        $this->assertSame('chocolate-cake', $recipe->getSlug());
    }

    public function testTransformSetsDescription(): void
    {
        $this->repo->shouldReceive('findOrCreate')->andReturn(new IngredientName());

        $recipe = $this->transformer->transform($this->sampleData());

        $this->assertSame('A delicious cake.', $recipe->getDescription());
    }

    public function testTransformSetsImage(): void
    {
        $this->repo->shouldReceive('findOrCreate')->andReturn(new IngredientName());

        $recipe = $this->transformer->transform($this->sampleData());

        $this->assertSame('https://example.com/image.jpg', $recipe->getImage());
    }

    public function testTransformCreatesComponents(): void
    {
        $this->repo->shouldReceive('findOrCreate')->andReturn(new IngredientName());

        $recipe = $this->transformer->transform($this->sampleData());

        $this->assertCount(1, $recipe->getComponents());
        $this->assertSame('For the batter', $recipe->getComponents()->first()->getName());
    }

    public function testTransformCreatesIngredients(): void
    {
        $ingredientName = new IngredientName();
        $ingredientName->setName('Flour');

        $this->repo->shouldReceive('findOrCreate')
            ->with('Flour')
            ->andReturn($ingredientName);

        $recipe = $this->transformer->transform($this->sampleData());

        $component = $recipe->getComponents()->first();
        $this->assertCount(1, $component->getIngredients());
        $this->assertSame('200g', $component->getIngredients()->first()->getMeasurement());
    }

    public function testTransformCreatesSteps(): void
    {
        $this->repo->shouldReceive('findOrCreate')->andReturn(new IngredientName());

        $recipe = $this->transformer->transform($this->sampleData());

        $this->assertCount(1, $recipe->getSteps());
        $this->assertSame('Mix all ingredients.', $recipe->getSteps()->first()->getDetail());
    }

    public function testTransformHandlesMissingOptionalFields(): void
    {
        $this->repo->shouldReceive('findOrCreate')->andReturn(new IngredientName());

        $data = [
            'title' => 'Simple Recipe',
        ];

        $recipe = $this->transformer->transform($data);

        $this->assertSame('Simple Recipe', $recipe->getName());
        $this->assertSame('no-slug', $recipe->getSlug());
        $this->assertSame('No description', $recipe->getDescription());
        $this->assertNull($recipe->getImage());
        $this->assertCount(0, $recipe->getComponents());
        $this->assertCount(0, $recipe->getSteps());
    }

    private function sampleData(): array
    {
        return [
            'title' => 'Chocolate Cake',
            'slug' => 'chocolate-cake',
            'description' => [
                ['data' => ['value' => 'A delicious cake.']],
            ],
            'headlineImages' => [
                'thumbnail' => ['url' => 'https://example.com/image.jpg'],
            ],
            'recipeAttributes' => [
                'ingredients' => [
                    [
                        'heading' => 'For the batter',
                        'ingredients' => [
                            [
                                'ingredientText' => 'flour',
                                'metricQuantity' => '200',
                                'metricUnit' => 'g',
                            ],
                        ],
                    ],
                ],
                'method' => [
                    [
                        'content' => [
                            ['data' => ['value' => 'Mix all ingredients.']],
                        ],
                    ],
                ],
            ],
        ];
    }
}
