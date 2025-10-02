<?php

declare(strict_types=1);

namespace App\Transformer;

use App\Entity\Component;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\Step;

class RecipeSearchToEntity
{
    public function transform(array $data): Recipe
    {
        $recipe = new Recipe();
        $recipe->setName($data['title'] ?? 'No name')
            ->setSlug($data['slug'] ?? 'no-slug')
            ->setDescription(strip_tags($data['description'][0]['data']['value'] ?? 'No description'))
            ->setImage($data['headlineImages']['thumbnail']['url'] ?? null);

        $this->addComponents($recipe, $data['recipeAttributes']['ingredients'] ?? []);
        $this->addMethod($recipe, $data['recipeAttributes']['method'] ?? []);

        return $recipe;
    }

    private function addComponents(Recipe $recipe, array $components): void
    {
        foreach ($components as $component) {
            $componentEntity = new Component();
            if (isset($component['heading'])) {
                $componentEntity->setName($component['heading']);
            }
            foreach ($component['ingredients'] as $ingredient) {
                if (!isset($ingredient['ingredientText'])) {
                    continue;
                }
                $ingredientEnt = new Ingredient();
                $ingredientEnt->setName(ucfirst($ingredient['ingredientText']));
                $ingredientEnt->setMeasurement(
                    isset($ingredient['metricQuantity'], $ingredient['metricUnit']) ?
                    $ingredient['metricQuantity'] . $ingredient['metricUnit'] :
                    $ingredient['quantityText'] ?? ''
                );
                $ingredientEnt->setNote($ingredient['note'] ?? null);
                $ingredientEnt->setComponent($componentEntity);

                $componentEntity->addIngredient($ingredientEnt);
            }
            $componentEntity->setRecipe($recipe);
            $recipe->addComponent($componentEntity);
        }
    }

    private function addMethod(Recipe $recipe, mixed $steps)
    {
        foreach ($steps as $step) {
            $stepEntity = new Step();
            $stepEntity->setRecipe($recipe);
            $stepEntity->setDetail(strip_tags($step['content'][0]['data']['value']) ?? '');
            $recipe->addStep($stepEntity);
        }
    }
}
