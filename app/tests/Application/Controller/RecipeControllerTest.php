<?php

declare(strict_types=1);

namespace App\Tests\Application\Controller;

use App\Entity\Recipe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RecipeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testIndexReturns200(): void
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    public function testIndexDisplaysRecipeName(): void
    {
        $this->seedRecipe('Pasta Bolognese');

        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Pasta Bolognese');
    }

    public function testShowReturns200ForExistingSlug(): void
    {
        $this->seedRecipe('Chicken Soup');

        $this->client->request('GET', '/chicken-soup');

        $this->assertResponseIsSuccessful();
    }

    public function testShowDisplaysRecipeName(): void
    {
        $this->seedRecipe('Chicken Soup');

        $this->client->request('GET', '/chicken-soup');

        $this->assertSelectorTextContains('body', 'Chicken Soup');
    }

    public function testShowReturns404ForUnknownSlug(): void
    {
        $this->client->request('GET', '/does-not-exist');

        $this->assertResponseStatusCodeSame(404);
    }

    private function seedRecipe(string $name): Recipe
    {
        $recipe = new Recipe();
        $recipe->setName($name);
        $this->entityManager->persist($recipe);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return $recipe;
    }
}
