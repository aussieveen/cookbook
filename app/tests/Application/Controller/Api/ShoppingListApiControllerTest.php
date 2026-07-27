<?php

declare(strict_types=1);

namespace App\Tests\Application\Controller\Api;

use App\Entity\Component;
use App\Entity\Ingredient;
use App\Entity\IngredientName;
use App\Entity\Recipe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ShoppingListApiControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testReturnsConsolidatedShoppingList(): void
    {
        $recipe = $this->seedRecipe('Pasta', 'Flour', 300.0, 'g');

        $this->client->request(
            'POST',
            '/api/v1/shopping-list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['recipeIds' => [$recipe->getId()]])
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertSame('Flour', $data[0]['name']);
        $this->assertSame('300g', $data[0]['display']);
    }

    public function testUnknownIdsAreIgnored(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/shopping-list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['recipeIds' => [999999]])
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame([], $data);
    }

    public function testEmptyRecipeIdsReturnsEmptyList(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/shopping-list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['recipeIds' => []])
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame([], $data);
    }

    public function testSumsAcrossMultipleRecipes(): void
    {
        // Share the same IngredientName to test summing across recipes
        $iname = new IngredientName();
        $iname->setName('Flour');
        $this->entityManager->persist($iname);

        $r1 = $this->seedRecipeWithSharedIngredient('Bread', $iname, 500.0, 'g');
        $r2 = $this->seedRecipeWithSharedIngredient('Cake', $iname, 200.0, 'g');

        $this->client->request(
            'POST',
            '/api/v1/shopping-list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['recipeIds' => [$r1->getId(), $r2->getId()]])
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertSame('700g', $data[0]['display']);
    }

    private function seedRecipe(string $name, string $ingredientName, float $quantity, string $unit): Recipe
    {
        $iname = new IngredientName();
        $iname->setName($ingredientName);

        $ingredient = new Ingredient();
        $ingredient->setIngredientName($iname);
        $ingredient->setMeasurement($quantity . $unit);

        $component = new Component();
        $component->setName('Main');
        $component->addIngredient($ingredient);

        $recipe = new Recipe();
        $recipe->setName($name);
        $component->setRecipe($recipe);
        $recipe->addComponent($component);

        $this->entityManager->persist($iname);
        $this->entityManager->persist($recipe);
        $this->entityManager->flush();

        return $recipe;
    }

    private function seedRecipeWithSharedIngredient(
        string $name,
        IngredientName $iname,
        float $quantity,
        string $unit
    ): Recipe {
        $ingredient = new Ingredient();
        $ingredient->setIngredientName($iname);
        $ingredient->setMeasurement($quantity . $unit);

        $component = new Component();
        $component->setName('Main');
        $component->addIngredient($ingredient);

        $recipe = new Recipe();
        $recipe->setName($name);
        $component->setRecipe($recipe);
        $recipe->addComponent($component);

        $this->entityManager->persist($recipe);
        $this->entityManager->flush();

        return $recipe;
    }
}
