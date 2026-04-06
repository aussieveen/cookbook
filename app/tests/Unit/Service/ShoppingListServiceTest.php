<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Component;
use App\Entity\Ingredient;
use App\Entity\IngredientName;
use App\Entity\Recipe;
use App\Entity\ShoppingListItem;
use App\Service\ShoppingListService;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class ShoppingListServiceTest extends TestCase
{
    private ShoppingListService $service;

    protected function setUp(): void
    {
        $this->service = new ShoppingListService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testConsolidateReturnsEmptyArrayWhenNoItems(): void
    {
        $result = $this->service->consolidate([]);

        $this->assertSame([], $result);
    }

    public function testConsolidateSkipsItemWithNullRecipe(): void
    {
        /** @var ShoppingListItem&MockInterface $item */
        $item = Mockery::mock(ShoppingListItem::class);
        $item->shouldReceive('getRecipe')->andReturn(null);

        $result = $this->service->consolidate([$item]);

        $this->assertSame([], $result);
    }

    public function testConsolidateSkipsIngredientWithNullIngredientName(): void
    {
        $ingredientName = null;

        /** @var Ingredient&MockInterface $ingredient */
        $ingredient = Mockery::mock(Ingredient::class);
        $ingredient->shouldReceive('getIngredientName')->andReturn($ingredientName);

        /** @var Component&MockInterface $component */
        $component = Mockery::mock(Component::class);
        $component->shouldReceive('getIngredients')->andReturn(new ArrayCollection([$ingredient]));

        /** @var Recipe&MockInterface $recipe */
        $recipe = Mockery::mock(Recipe::class);
        $recipe->shouldReceive('getComponents')->andReturn(new ArrayCollection([$component]));

        /** @var ShoppingListItem&MockInterface $item */
        $item = Mockery::mock(ShoppingListItem::class);
        $item->shouldReceive('getRecipe')->andReturn($recipe);

        $result = $this->service->consolidate([$item]);

        $this->assertSame([], $result);
    }

    public function testConsolidateGroupsAndSumsIngredientsByNameId(): void
    {
        $items = [
            $this->makeItem(1, 'Flour', 300.0, 'g'),
            $this->makeItem(1, 'Flour', 200.0, 'g'),
        ];

        $result = $this->service->consolidate($items);

        $this->assertCount(1, $result);
        $this->assertSame('Flour', $result[0]['name']);
        $this->assertSame('500g', $result[0]['display']);
    }

    public function testConsolidateFormatsGramsAbove1000AsKg(): void
    {
        $result = $this->service->consolidate([
            $this->makeItem(1, 'Butter', 1500.0, 'g'),
        ]);

        $this->assertSame('1.5kg', $result[0]['display']);
    }

    public function testConsolidateFormatsMlAbove1000AsLitres(): void
    {
        $result = $this->service->consolidate([
            $this->makeItem(1, 'Milk', 1500.0, 'ml'),
        ]);

        $this->assertSame('1.5l', $result[0]['display']);
    }

    public function testConsolidateNullBaseQuantityProducesEmptyDisplay(): void
    {
        $result = $this->service->consolidate([
            $this->makeItem(1, 'Salt', null, null),
        ]);

        $this->assertSame('', $result[0]['display']);
    }

    public function testConsolidateSortsResultsAlphabetically(): void
    {
        $result = $this->service->consolidate([
            $this->makeItem(3, 'Zucchini', 1.0, null),
            $this->makeItem(1, 'Apple', 1.0, null),
            $this->makeItem(2, 'Banana', 1.0, null),
        ]);

        $this->assertSame('Apple', $result[0]['name']);
        $this->assertSame('Banana', $result[1]['name']);
        $this->assertSame('Zucchini', $result[2]['name']);
    }

    public function testConsolidateNullBaseUnitShowsPlainNumber(): void
    {
        $result = $this->service->consolidate([
            $this->makeItem(1, 'Eggs', 3.0, null),
        ]);

        $this->assertSame('3', $result[0]['display']);
    }

    /**
     * Helper to build a ShoppingListItem mock containing one ingredient.
     */
    private function makeItem(int $nameId, string $name, ?float $baseQuantity, ?string $baseUnit): ShoppingListItem
    {
        /** @var IngredientName&MockInterface $ingredientName */
        $ingredientName = Mockery::mock(IngredientName::class);
        $ingredientName->shouldReceive('getId')->andReturn($nameId);
        $ingredientName->shouldReceive('getName')->andReturn($name);

        /** @var Ingredient&MockInterface $ingredient */
        $ingredient = Mockery::mock(Ingredient::class);
        $ingredient->shouldReceive('getIngredientName')->andReturn($ingredientName);
        $ingredient->shouldReceive('getBaseQuantity')->andReturn($baseQuantity);
        $ingredient->shouldReceive('getBaseUnit')->andReturn($baseUnit);

        /** @var Component&MockInterface $component */
        $component = Mockery::mock(Component::class);
        $component->shouldReceive('getIngredients')->andReturn(new ArrayCollection([$ingredient]));

        /** @var Recipe&MockInterface $recipe */
        $recipe = Mockery::mock(Recipe::class);
        $recipe->shouldReceive('getComponents')->andReturn(new ArrayCollection([$component]));

        /** @var ShoppingListItem&MockInterface $item */
        $item = Mockery::mock(ShoppingListItem::class);
        $item->shouldReceive('getRecipe')->andReturn($recipe);

        return $item;
    }
}
