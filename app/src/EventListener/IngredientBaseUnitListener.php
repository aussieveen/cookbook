<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Ingredient;
use App\Service\UnitConverterService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: Ingredient::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Ingredient::class)]
class IngredientBaseUnitListener
{
    public function __construct(
        private UnitConverterService $unitConverter
    ) {
    }

    public function prePersist(Ingredient $ingredient): void
    {
        $this->updateBaseUnit($ingredient);
    }

    public function preUpdate(Ingredient $ingredient): void
    {
        $this->updateBaseUnit($ingredient);
    }

    private function updateBaseUnit(Ingredient $ingredient): void
    {
        $measurement = $ingredient->getRevisedMeasurement() ?? $ingredient->getMeasurement();
        if ($measurement === null) {
            return;
        }

        $result = $this->unitConverter->convert($measurement);
        $ingredient->setBaseQuantity($result['quantity']);
        $ingredient->setBaseUnit($result['unit']);
    }
}
