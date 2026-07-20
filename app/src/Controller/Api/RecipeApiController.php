<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Recipe;
use App\Enum\Course;
use App\Enum\MealOccasion;
use App\Repository\RecipeRepository;
use BackedEnum;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1', format: 'json')]
#[OA\Tag(name: 'Recipes')]
class RecipeApiController extends AbstractController
{
    public function __construct(private RecipeRepository $recipeRepository)
    {
    }

    #[Route('/recipes', name: 'api_recipes_index', methods: ['GET'])]
    #[OA\Get(
        summary: 'List and search recipes',
        description: 'Returns recipes filtered by ingredients, meal occasion, and/or course. '
            . 'All filters are optional and combinable.',
    )]
    #[OA\Parameter(
        name: 'q',
        description: 'Search by recipe name (partial match)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'ingredients[]',
        description: 'Filter by ingredient name (partial match). '
            . 'Repeat for multiple: ?ingredients[]=sausage&ingredients[]=pasta',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string'))
    )]
    #[OA\Parameter(
        name: 'meal_occasion',
        description: 'Filter by meal occasion',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['breakfast', 'brunch', 'lunch', 'dinner', 'supper', 'snack'])
    )]
    #[OA\Parameter(
        name: 'course',
        description: 'Filter by course type',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
            enum: ['starter', 'main', 'side', 'dessert']
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Array of recipe summaries (id, name, slug, course, mealOccasions, mastered)',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Recipe::class, groups: ['recipe:summary']))
        )
    )]
    public function index(Request $request): JsonResponse
    {
        $ingredientNames = $request->query->all('ingredients');
        $nameQuery       = $request->query->getString('q') ?: null;
        $mealOccasion    = $this->enumFromQuery($request, 'meal_occasion', MealOccasion::class);
        $course          = $this->enumFromQuery($request, 'course', Course::class);

        $recipes = $this->recipeRepository->search($ingredientNames, $mealOccasion, $course, $nameQuery);

        return $this->json($recipes, context: ['groups' => ['recipe:summary']]);
    }

    #[Route('/recipes/{id}', name: 'api_recipes_show', methods: ['GET'])]
    #[OA\Get(summary: 'Get a single recipe with full detail')]
    #[OA\Parameter(
        name: 'id',
        description: 'Recipe ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Full recipe detail including components, ingredients, steps, and pairings',
        content: new OA\JsonContent(ref: new Model(type: Recipe::class, groups: ['recipe:detail']))
    )]
    #[OA\Response(response: 404, description: 'Recipe not found')]
    public function show(Recipe $recipe): JsonResponse
    {
        return $this->json($recipe, context: ['groups' => ['recipe:detail']]);
    }

    #[Route('/recipes/{id}/suggested-sides', name: 'api_recipes_suggested_sides', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get suggested sides for a recipe',
        description: 'Returns explicit pairings filtered to course=side. '
            . 'Falls back to all side-course recipes if no explicit pairings exist.',
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Recipe ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Array of recipe summaries',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Recipe::class, groups: ['recipe:summary']))
        )
    )]
    #[OA\Response(response: 404, description: 'Recipe not found')]
    public function suggestedSides(Recipe $recipe): JsonResponse
    {
        $sides = $recipe->getAllPairings()->filter(
            fn(Recipe $r) => $r->getCourse() === Course::SIDE
        );

        if ($sides->isEmpty()) {
            $sides = $this->recipeRepository->search(course: Course::SIDE);

            return $this->json($sides, context: ['groups' => ['recipe:summary']]);
        }

        return $this->json($sides->getValues(), context: ['groups' => ['recipe:summary']]);
    }

    /** @param class-string<BackedEnum> $enumClass */
    private function enumFromQuery(Request $request, string $param, string $enumClass): ?BackedEnum
    {
        $value = $request->query->get($param);

        return $value !== null ? $enumClass::tryFrom($value) : null;
    }
}
