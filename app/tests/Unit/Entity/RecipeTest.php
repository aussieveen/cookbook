<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Component;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Enum\RecipeCategory;
use App\Enum\RecipeType;
use PHPUnit\Framework\TestCase;

class RecipeTest extends TestCase
{
    public function testDeriveTypeSetsItemWhenOneComponentAndOneIngredient(): void
    {
        $component = new Component();
        $component->addIngredient(new Ingredient());

        $recipe = new Recipe();
        $recipe->addComponent($component);
        $recipe->deriveType();

        $this->assertSame(RecipeType::ITEM, $recipe->getType());
        $this->assertTrue($recipe->isItem());
    }

    public function testDeriveTypeSetsRecipeWhenOneComponentWithMultipleIngredients(): void
    {
        $component = new Component();
        $component->addIngredient(new Ingredient());
        $component->addIngredient(new Ingredient());

        $recipe = new Recipe();
        $recipe->addComponent($component);
        $recipe->deriveType();

        $this->assertSame(RecipeType::RECIPE, $recipe->getType());
        $this->assertFalse($recipe->isItem());
    }

    public function testDeriveTypeSetsRecipeWhenMultipleComponents(): void
    {
        $recipe = new Recipe();
        $recipe->addComponent(new Component());
        $recipe->addComponent(new Component());
        $recipe->deriveType();

        $this->assertSame(RecipeType::RECIPE, $recipe->getType());
        $this->assertFalse($recipe->isItem());
    }

    public function testDeriveTypeSetsRecipeWhenNoComponents(): void
    {
        $recipe = new Recipe();
        $recipe->deriveType();

        $this->assertSame(RecipeType::RECIPE, $recipe->getType());
    }

    public function testSetAndGetRecipeCategoriesRoundTrip(): void
    {
        $recipe = new Recipe();
        $recipe->setRecipeCategories([RecipeCategory::DINNER, RecipeCategory::LUNCH]);

        $this->assertSame([RecipeCategory::DINNER, RecipeCategory::LUNCH], $recipe->getRecipeCategories());
    }

    public function testSetRecipeCategoriesStoresStrings(): void
    {
        $recipe = new Recipe();
        $recipe->setRecipeCategories([RecipeCategory::BREAKFAST]);

        // Internal storage is strings — serializer and Doctrine JSON column read raw array
        $raw = (fn() => $this->recipeCategories)->call($recipe);
        $this->assertSame(['breakfast'], $raw);
    }

    public function testGetAllPairingsMergesBothSides(): void
    {
        $main = new Recipe();
        $side = new Recipe();
        $other = new Recipe();

        $main->addPairsWith($side);

        // Simulate Doctrine populating the inverse side
        (function () use ($other) {
            $this->pairedBy->add($other);
        })->call($main);

        $all = $main->getAllPairings();

        $this->assertCount(2, $all);
        $this->assertTrue($all->contains($side));
        $this->assertTrue($all->contains($other));
    }

    public function testGetAllPairingsDeduplicates(): void
    {
        $main = new Recipe();
        $paired = new Recipe();

        $main->addPairsWith($paired);
        // Same recipe appears on both sides
        (function () use ($paired) {
            $this->pairedBy->add($paired);
        })->call($main);

        $this->assertCount(1, $main->getAllPairings());
    }
}
