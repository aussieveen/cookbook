<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\Entity\Ingredient;
use App\EventListener\IngredientBaseUnitListener;
use App\Service\UnitConverterService;
use PHPUnit\Framework\TestCase;

class IngredientBaseUnitListenerTest extends TestCase
{
    private IngredientBaseUnitListener $listener;

    protected function setUp(): void
    {
        $this->listener = new IngredientBaseUnitListener(new UnitConverterService());
    }

    public function testPrePersistSetsBaseUnitFromMeasurement(): void
    {
        $ingredient = new Ingredient();
        $ingredient->setMeasurement('500g');

        $this->listener->prePersist($ingredient);

        $this->assertSame(500.0, $ingredient->getBaseQuantity());
        $this->assertSame('g', $ingredient->getBaseUnit());
    }

    public function testPreUpdateSetsBaseUnitFromMeasurement(): void
    {
        $ingredient = new Ingredient();
        $ingredient->setMeasurement('250ml');

        $this->listener->preUpdate($ingredient);

        $this->assertSame(250.0, $ingredient->getBaseQuantity());
        $this->assertSame('ml', $ingredient->getBaseUnit());
    }

    public function testPrePersistPrefersRevisedMeasurementOverMeasurement(): void
    {
        $ingredient = new Ingredient();
        $ingredient->setMeasurement('2oz');
        $ingredient->setRevisedMeasurement('2tbsp');

        $this->listener->prePersist($ingredient);

        $this->assertSame(30.0, $ingredient->getBaseQuantity());
        $this->assertSame('ml', $ingredient->getBaseUnit());
    }

    public function testPrePersistDoesNothingWhenMeasurementIsNull(): void
    {
        $ingredient = new Ingredient();

        $this->listener->prePersist($ingredient);

        $this->assertNull($ingredient->getBaseQuantity());
        $this->assertNull($ingredient->getBaseUnit());
    }

    public function testPrePersistSetsNullsForUnrecognisedUnit(): void
    {
        $ingredient = new Ingredient();
        $ingredient->setMeasurement('3oz');

        $this->listener->prePersist($ingredient);

        $this->assertNull($ingredient->getBaseQuantity());
        $this->assertNull($ingredient->getBaseUnit());
    }
}
