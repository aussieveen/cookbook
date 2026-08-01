<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\IngredientName;
use App\Enum\IngredientCategory;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1', format: 'json')]
#[OA\Tag(name: 'Ingredient Names')]
class IngredientNameApiController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('/ingredient-names/categories', name: 'api_ingredient_name_categories', methods: ['GET'])]
    #[OA\Get(summary: 'List available ingredient categories')]
    #[OA\Response(
        response: 200,
        description: 'Array of category options',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'value', type: 'string', example: 'produce'),
                    new OA\Property(property: 'label', type: 'string', example: 'Produce'),
                ]
            )
        )
    )]
    public function categories(): JsonResponse
    {
        return $this->json(array_map(
            fn(IngredientCategory $c) => ['value' => $c->value, 'label' => $c->label()],
            IngredientCategory::cases()
        ));
    }

    #[Route('/ingredient-names/{id}/category', name: 'api_ingredient_name_category', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Set the category for an ingredient name',
        description: 'Updates the shopping category for an ingredient. '
            . 'Valid values: produce, meat_and_fish, dairy_and_chilled, store_cupboard. '
            . 'Send null to clear the category.',
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'category',
                    type: 'string',
                    nullable: true,
                    enum: ['produce', 'meat_and_fish', 'dairy_and_chilled', 'store_cupboard'],
                    example: 'produce'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Updated ingredient name',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'category', type: 'string', nullable: true),
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Invalid category value')]
    #[OA\Response(response: 404, description: 'Ingredient name not found')]
    public function updateCategory(IngredientName $ingredientName, Request $request): JsonResponse
    {
        $body     = json_decode($request->getContent(), true) ?? [];
        $rawValue = array_key_exists('category', $body) ? $body['category'] : false;

        if ($rawValue === false) {
            return $this->json(['error' => 'Missing "category" key'], 400);
        }

        if ($rawValue === null) {
            $ingredientName->setCategory(null);
            $this->em->flush();

            return $this->json([
                'id'       => $ingredientName->getId(),
                'name'     => $ingredientName->getName(),
                'category' => null,
            ]);
        }

        $category = IngredientCategory::tryFrom($rawValue);
        if ($category === null) {
            $valid = implode(', ', array_column(IngredientCategory::cases(), 'value'));

            return $this->json(['error' => "Invalid category. Valid values: $valid"], 400);
        }
        $ingredientName->setCategory($category);

        $this->em->flush();

        return $this->json([
            'id'       => $ingredientName->getId(),
            'name'     => $ingredientName->getName(),
            'category' => $ingredientName->getCategory()?->value,
        ]);
    }
}
