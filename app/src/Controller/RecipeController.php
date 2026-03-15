<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use App\Repository\ShoppingListItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function index(): Response
    {
        $shoppingListItems = $this->shoppingListItemRepository->findAll();
        $shoppingListRecipeIds = array_map(
            fn ($item) => $item->getRecipe()?->getId(),
            $shoppingListItems
        );

        return $this->render('recipe/index.html.twig', [
            'recipes'               => $this->recipeRepository->findBy([], ['name' => 'DESC']),
            'shoppingListRecipeIds' => $shoppingListRecipeIds,
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
}
