<?php

namespace App\Controller;

use App\Enum\Course;
use App\Enum\RecipeCategory;
use App\Repository\RecipeRepository;
use App\Repository\ShoppingListItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RecipeController extends AbstractController
{
    public function __construct(
        private RecipeRepository $recipeRepository,
        private ShoppingListItemRepository $shoppingListItemRepository
    ) {
    }

    #[Route(name: 'home', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $shoppingListItems = $this->shoppingListItemRepository->findAll();
        $shoppingListRecipeIds = array_map(
            fn ($item) => $item->getRecipe()?->getId(),
            $shoppingListItems
        );

        $selectedCourseValues   = $this->flattenQueryArray($request->query->all('courses'));
        $selectedCategoryValues = $this->flattenQueryArray($request->query->all('categories'));

        /** @var Course[] $selectedCourses */
        $selectedCourses = array_values(array_filter(
            array_map(fn(string $v) => Course::tryFrom($v), $selectedCourseValues)
        ));

        /** @var RecipeCategory[] $selectedCategories */
        $selectedCategories = array_values(array_filter(
            array_map(fn(string $v) => RecipeCategory::tryFrom($v), $selectedCategoryValues)
        ));

        $recipes = $this->recipeRepository->findFiltered($selectedCourses, $selectedCategories);

        return $this->render('recipe/index.html.twig', [
            'recipes'               => $recipes,
            'shoppingListRecipeIds' => $shoppingListRecipeIds,
            'allCourses'            => Course::cases(),
            'allCategories'         => RecipeCategory::cases(),
            'selectedCourses'       => array_map(fn(Course $c) => $c->value, $selectedCourses),
            'selectedCategories'    => array_map(fn(RecipeCategory $c) => $c->value, $selectedCategories),
        ]);
    }

    #[Route('/{slug}', name: 'recipe_show', methods: ['GET'])]
    public function show(string $slug): Response
    {
        $recipe = $this->recipeRepository->findOneBy(['slug' => $slug]);

        if ($recipe === null) {
            throw $this->createNotFoundException("Recipe '$slug' not found.");
        }

        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }

    /**
     * Flatten one level of nesting that can occur when Symfony's path() serialises
     * non-sequential arrays as bracketed keys (e.g. courses[0][0]=main).
     *
     * @param mixed[] $values
     * @return string[]
     */
    private function flattenQueryArray(array $values): array
    {
        $flat = [];
        array_walk_recursive($values, function (mixed $v) use (&$flat): void {
            if (is_string($v)) {
                $flat[] = $v;
            }
        });

        return $flat;
    }
}
