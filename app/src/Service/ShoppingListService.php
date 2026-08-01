<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Recipe;

class ShoppingListService
{
    /**
     * @param Recipe[] $recipes
     * @return array<array{id: int, name: string, category: string|null, display: string}>
     */
    public function consolidate(array $recipes): array
    {
        $groups = [];

        foreach ($recipes as $recipe) {
            foreach ($recipe->getComponents() as $component) {
                foreach ($component->getIngredients() as $ingredient) {
                    $ingredientName = $ingredient->getIngredientName();
                    if ($ingredientName === null) {
                        continue;
                    }

                    $nameId = $ingredientName->getId();
                    $name   = $ingredientName->getName() ?? '';

                    if (!isset($groups[$nameId])) {
                        $groups[$nameId] = [
                            'id'            => $nameId,
                            'name'          => $name,
                            'category'      => $ingredientName->getCategory()?->value,
                            'totalQuantity' => 0.0,
                            'unit'          => $ingredient->getBaseUnit(),
                            'unmeasurable'  => false,
                        ];
                    }

                    $baseQuantity = $ingredient->getBaseQuantity();
                    if ($baseQuantity === null) {
                        $groups[$nameId]['unmeasurable'] = true;
                        continue;
                    }
                    $groups[$nameId]['totalQuantity'] += $baseQuantity;
                }
            }
        }

        $result = [];
        foreach ($groups as $group) {
            $result[] = [
                'id'       => $group['id'],
                'name'     => $group['name'],
                'category' => $group['category'],
                'display'  => $this->formatDisplay($group),
            ];
        }

        usort($result, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $result;
    }

    /**
     * @param array{name: string, category: string|null, totalQuantity: float, unit: ?string, unmeasurable: bool} $group
     */
    private function formatDisplay(array $group): string
    {
        if ($group['unmeasurable'] || $group['totalQuantity'] === 0.0) {
            return '';
        }

        $qty  = $group['totalQuantity'];
        $unit = $group['unit'];

        if ($unit === 'g' && $qty >= 1000) {
            return ($qty / 1000) . 'kg';
        }

        if ($unit === 'ml' && $qty >= 1000) {
            return ($qty / 1000) . 'l';
        }

        if ($unit === null) {
            return (string)$qty;
        }

        return $qty . $unit;
    }
}
