<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ShoppingListItem;
use App\Repository\RecipeRepository;
use App\Repository\ShoppingListItemRepository;
use App\Service\ShoppingListService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/shopping-list', priority: 2)]
final class ShoppingListController extends AbstractController
{
    public function __construct(
        private ShoppingListItemRepository $shoppingListItemRepository,
        private RecipeRepository $recipeRepository,
        private ShoppingListService $shoppingListService,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'shopping_list_index', methods: ['GET'])]
    public function index(): Response
    {
        $items       = $this->shoppingListItemRepository->findAllWithRecipes();
        $consolidated = $this->shoppingListService->consolidate($items);

        return $this->render('shopping_list/index.html.twig', [
            'items'        => $items,
            'consolidated' => $consolidated,
        ]);
    }

    #[Route('/add/{id}', name: 'shopping_list_add', methods: ['POST'])]
    public function add(int $id): Response
    {
        $recipe = $this->recipeRepository->find($id);

        if ($recipe !== null && $this->shoppingListItemRepository->findByRecipe($recipe) === null) {
            $item = new ShoppingListItem();
            $item->setRecipe($recipe);
            $this->entityManager->persist($item);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('home');
    }

    #[Route('/remove/{id}', name: 'shopping_list_remove', methods: ['POST'])]
    public function remove(int $id, Request $request): Response
    {
        $recipe = $this->recipeRepository->find($id);

        if ($recipe !== null) {
            $item = $this->shoppingListItemRepository->findByRecipe($recipe);
            if ($item !== null) {
                $this->entityManager->remove($item);
                $this->entityManager->flush();
            }
        }

        $referer = $request->headers->get('referer');
        if ($referer !== null && str_contains($referer, '/shopping-list')) {
            return $this->redirectToRoute('shopping_list_index');
        }

        return $this->redirectToRoute('home');
    }

    #[Route('/clear', name: 'shopping_list_clear', methods: ['POST'])]
    public function clear(): Response
    {
        foreach ($this->shoppingListItemRepository->findAll() as $item) {
            $this->entityManager->remove($item);
        }
        $this->entityManager->flush();

        return $this->redirectToRoute('shopping_list_index');
    }
}
