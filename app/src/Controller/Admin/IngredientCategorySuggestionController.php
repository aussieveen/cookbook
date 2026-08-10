<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\IngredientCategorySuggestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/ingredient-names/category-suggestion', name: 'admin_ingredient_category_suggestion')]
class IngredientCategorySuggestionController extends AbstractController
{
    public function __construct(
        private readonly IngredientCategorySuggestionRepository $repository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/apply/{id}', name: '_apply', methods: ['POST'])]
    public function apply(int $id): Response
    {
        $suggestion = $this->repository->find($id);

        if ($suggestion !== null) {
            $suggestion->getIngredientName()->setCategory($suggestion->getSuggestedCategory());
            $suggestion->dismiss();
            $this->entityManager->flush();
            $this->addFlash('success', sprintf(
                'Applied category "%s" to "%s".',
                $suggestion->getSuggestedCategory()->label(),
                $suggestion->getIngredientName()->getName(),
            ));
        }

        return $this->redirectToRoute('admin', [
            'crudControllerFqcn' => IngredientNameCrudController::class,
            'crudAction' => 'index',
        ]);
    }

    #[Route('/dismiss/{id}', name: '_dismiss', methods: ['POST'])]
    public function dismiss(int $id): Response
    {
        $suggestion = $this->repository->find($id);

        if ($suggestion !== null) {
            $suggestion->dismiss();
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('admin', [
            'crudControllerFqcn' => IngredientNameCrudController::class,
            'crudAction' => 'index',
        ]);
    }
}
