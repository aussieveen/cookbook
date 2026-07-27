<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\RecipeRepository;
use App\Service\ShoppingListService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1', format: 'json')]
#[OA\Tag(name: 'Shopping List')]
class ShoppingListApiController extends AbstractController
{
    public function __construct(
        private RecipeRepository $recipeRepository,
        private ShoppingListService $shoppingListService,
    ) {
    }

    #[Route('/shopping-list', name: 'api_shopping_list', methods: ['POST'])]
    #[OA\Post(
        summary: 'Get a consolidated shopping list for a set of recipes',
        description: 'Accepts a list of recipe IDs and returns a consolidated ingredient list '
            . 'with quantities summed and units normalised. Unknown IDs are silently ignored.',
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'recipeIds',
                    type: 'array',
                    items: new OA\Items(type: 'integer'),
                    example: [1, 2, 3]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Consolidated ingredient list sorted alphabetically',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Flour'),
                    new OA\Property(property: 'display', type: 'string', example: '500g'),
                ]
            )
        )
    )]
    public function shoppingList(Request $request): JsonResponse
    {
        $body    = json_decode($request->getContent(), true) ?? [];
        $ids     = array_map('intval', $body['recipeIds'] ?? []);
        $recipes = $ids !== [] ? $this->recipeRepository->findBy(['id' => $ids]) : [];

        return $this->json($this->shoppingListService->consolidate($recipes));
    }
}
