<?php

declare(strict_types=1);

namespace App\Tests\Application\Controller\Api;

use App\Entity\Component;
use App\Entity\Ingredient;
use App\Entity\IngredientName;
use App\Entity\Recipe;
use App\Entity\Step;
use App\Enum\Course;
use App\Enum\MealOccasion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RecipeApiControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testIndexReturns200WithJsonArray(): void
    {
        $this->seedRecipe('Pasta');

        $this->client->request('GET', '/api/v1/recipes');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testIndexFiltersByCourse(): void
    {
        $this->seedRecipe('Roast Lamb', course: Course::MAIN);
        $this->seedRecipe('Ice Cream', course: Course::DESSERT);

        $this->client->request('GET', '/api/v1/recipes?course=main');
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $data);
        $this->assertSame('Roast Lamb', $data[0]['name']);
    }

    public function testIndexFiltersByMealOccasion(): void
    {
        $this->seedRecipe('Pancakes', mealOccasions: [MealOccasion::BREAKFAST]);
        $this->seedRecipe('Steak', mealOccasions: [MealOccasion::DINNER]);

        $this->client->request('GET', '/api/v1/recipes?meal_occasion=breakfast');
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $data);
        $this->assertSame('Pancakes', $data[0]['name']);
    }

    public function testIndexFiltersByIngredient(): void
    {
        $this->seedRecipe('Pasta Bolognese', ingredientName: 'pasta');
        $this->seedRecipe('Green Salad');

        $this->client->request('GET', '/api/v1/recipes?ingredients[]=pasta');
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $data);
        $this->assertSame('Pasta Bolognese', $data[0]['name']);
    }

    public function testSummaryFieldsPresent(): void
    {
        $this->seedRecipe('Test Recipe', course: Course::MAIN);

        $this->client->request('GET', '/api/v1/recipes');
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('slug', $data[0]);
        $this->assertArrayHasKey('course', $data[0]);
        $this->assertArrayHasKey('mealOccasions', $data[0]);
        $this->assertArrayHasKey('mastered', $data[0]);
        $this->assertArrayNotHasKey('description', $data[0]);
        $this->assertArrayNotHasKey('components', $data[0]);
    }

    public function testShowReturns200WithDetailFields(): void
    {
        $recipe = $this->seedRecipe('Chicken Soup');

        $this->client->request('GET', '/api/v1/recipes/' . $recipe->getId());
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('components', $data);
        $this->assertArrayHasKey('steps', $data);
    }

    public function testShowReturns404ForMissingId(): void
    {
        $this->client->request('GET', '/api/v1/recipes/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSuggestedSidesReturnsExplicitPairings(): void
    {
        $main = $this->seedRecipe('Roast Chicken', course: Course::MAIN);
        $side = $this->seedRecipe('Roasted Potatoes', course: Course::SIDE);
        $main->addPairsWith($side);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/v1/recipes/' . $main->getId() . '/suggested-sides');
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $data);
        $this->assertSame('Roasted Potatoes', $data[0]['name']);
    }

    public function testSuggestedSidesFallsBackToAllSides(): void
    {
        $main  = $this->seedRecipe('Pasta', course: Course::MAIN);
        $this->seedRecipe('Green Beans', course: Course::SIDE);
        $this->seedRecipe('Mashed Potato', course: Course::SIDE);

        $this->client->request('GET', '/api/v1/recipes/' . $main->getId() . '/suggested-sides');
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertCount(2, $data);
    }

    private function seedRecipe(
        string $name,
        ?Course $course = null,
        array $mealOccasions = [],
        ?string $ingredientName = null,
    ): Recipe {
        $recipe = new Recipe();
        $recipe->setName($name);
        $recipe->setCourse($course);
        $recipe->setMealOccasions($mealOccasions);

        if ($ingredientName !== null) {
            $iname = new IngredientName();
            $iname->setName($ingredientName);

            $ingredient = new Ingredient();
            $ingredient->setIngredientName($iname);
            $ingredient->setMeasurement('100g');

            $component = new Component();
            $component->setName('Main');
            $component->addIngredient($ingredient);
            $component->setRecipe($recipe);

            $recipe->addComponent($component);

            $this->entityManager->persist($iname);
        }

        $this->entityManager->persist($recipe);
        $this->entityManager->flush();

        return $recipe;
    }
}
