<?php

namespace App\Controller\Form;

use App\Repository\RecipeRepository;
use App\Transformer\RecipeSearchToEntity;
use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImportRecipeFormHandler
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly RecipeSearchToEntity $recipeTransformer,
        private readonly RecipeRepository $recipeRepository,
        private readonly string $apiUrlFormat,
    ) {
    }

    public function handle(string $slug): void
    {
        $response = $this->client->request(
            'GET',
            sprintf($this->apiUrlFormat, $slug)
        );

        try {
            $recipe = $this->recipeTransformer->transform($response->toArray()['hydra:member'][0]);
        } catch (Exception $exception) {
            throw new Exception('Recipe not found or invalid data structure.');
        }

        $this->recipeRepository->save($recipe);
    }
}
