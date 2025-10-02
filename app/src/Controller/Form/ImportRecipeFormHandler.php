<?php

namespace App\Controller\Form;

use App\Repository\RecipeRepository;
use App\Transformer\RecipeSearchToEntity;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImportRecipeFormHandler
{
    private const string URL_FORMAT = 'https://search.api.immediate.co.uk/v5/search/bbcgoodfood' .
                                        '?filter[slug.keyword][match][]=%s&views=search-document:read';

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly RecipeSearchToEntity $recipeTransformer,
        private readonly RecipeRepository $recipeRepository,
    ) {
    }

    #[NoReturn]
    public function handle(string $slug): void
    {
        $response = $this->client->request(
            'GET',
            sprintf(self::URL_FORMAT, $slug)
        );

        try {
            $recipe = $this->recipeTransformer->transform($response->toArray()['hydra:member'][0]);
        } catch (Exception $exception) {
            throw new Exception('Recipe not found or invalid data structure.');
        }

        $this->recipeRepository->save($recipe);
    }
}
