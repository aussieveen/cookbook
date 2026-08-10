<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\IngredientName;
use App\Repository\IngredientCategorySuggestionRepository;
use App\Repository\IngredientRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class IngredientSuggestionExtension extends AbstractExtension
{
    public function __construct(
        private readonly IngredientCategorySuggestionRepository $categoryRepo,
        private readonly IngredientRepository $ingredientRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pending_category_suggestions', $this->categoryRepo->findPending(...)),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('recipes_for_ingredient_name', function (IngredientName $name): array {
                return $this->ingredientRepository->findRecipesByIngredientName($name);
            }),
        ];
    }
}
