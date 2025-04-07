<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RecipeController extends AbstractController
{
    public function __construct(
        private RecipeRepository $recipeRepository
    ) {
    }

    #[Route(name: 'home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('recipe/index.html.twig', [
            'recipes' => $this->recipeRepository->findBy([], ['name' => 'DESC'])
        ]);
    }

    #[Route('/{slug}', name: 'recipe_show', methods: ['GET'])]
    public function show(string $slug): Response
    {
        $recipe = $this->recipeRepository->findOneBy(['slug' => $slug]);

        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }
}
