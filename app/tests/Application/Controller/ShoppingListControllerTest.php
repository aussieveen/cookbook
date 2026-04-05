<?php

declare(strict_types=1);

namespace App\Tests\Application\Controller;

use App\Entity\Recipe;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ShoppingListControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testIndexReturns200(): void
    {
        $this->client->request('GET', '/shopping-list');

        $this->assertResponseIsSuccessful();
    }

    public function testAddRecipeToShoppingList(): void
    {
        $recipe = $this->seedRecipe('Lasagne');

        $this->client->request('POST', '/shopping-list/add/' . $recipe->getId());
        $this->client->request('GET', '/shopping-list');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Lasagne');
    }

    public function testAddDuplicateRecipeDoesNotCreateTwoItems(): void
    {
        $recipe = $this->seedRecipe('Risotto');

        $this->client->request('POST', '/shopping-list/add/' . $recipe->getId());
        $this->client->request('POST', '/shopping-list/add/' . $recipe->getId());

        $count = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM shopping_list_item');
        $this->assertSame(1, $count);
    }

    public function testAddUnknownRecipeRedirectsGracefully(): void
    {
        $this->client->request('POST', '/shopping-list/add/99999');

        $this->assertResponseRedirects('/');
    }

    public function testRemoveRecipeFromShoppingList(): void
    {
        $recipe = $this->seedRecipe('Tacos');

        $this->client->request('POST', '/shopping-list/add/' . $recipe->getId());
        $this->client->request('POST', '/shopping-list/remove/' . $recipe->getId());

        $count = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM shopping_list_item');
        $this->assertSame(0, $count);
    }

    public function testClearEmptiesShoppingList(): void
    {
        $recipe1 = $this->seedRecipe('Pizza');
        $recipe2 = $this->seedRecipe('Sushi');

        $this->client->request('POST', '/shopping-list/add/' . $recipe1->getId());
        $this->client->request('POST', '/shopping-list/add/' . $recipe2->getId());
        $this->client->request('POST', '/shopping-list/clear');

        $count = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM shopping_list_item');
        $this->assertSame(0, $count);
    }

    private function seedRecipe(string $name): Recipe
    {
        $recipe = new Recipe();
        $recipe->setName($name);
        $this->entityManager->persist($recipe);
        $this->entityManager->flush();

        return $recipe;
    }
}
