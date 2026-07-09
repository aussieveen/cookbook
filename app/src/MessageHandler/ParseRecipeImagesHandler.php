<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Component;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\Step;
use App\Message\ParseRecipeImagesMessage;
use App\Repository\IngredientNameRepository;
use App\Repository\RecipeRepository;
use App\Service\AiRecipeParser;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class ParseRecipeImagesHandler
{
    public function __construct(
        private readonly AiRecipeParser $parser,
        private readonly EntityManagerInterface $entityManager,
        private readonly IngredientNameRepository $ingredientNameRepository,
        private readonly RecipeRepository $recipeRepository,
        private readonly FilesystemOperator $activeStorage,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ParseRecipeImagesMessage $message): void
    {
        $storage = $this->activeStorage;

        $rawImages = [];
        $base64Images = [];
        foreach ($message->imageS3Keys as $key) {
            $raw = stream_get_contents($storage->readStream($key));
            $rawImages[] = $raw;
            $base64Images[] = base64_encode($raw);
        }

        try {
            $data = $this->parser->parse($base64Images);
        } catch (Throwable $e) {
            $this->logger->error('AI recipe parsing failed', [
                'keys' => $message->imageS3Keys,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        $recipe = new Recipe();
        $recipe->setName($data['name']);

        if ($this->recipeRepository->findOneBy(['name' => $data['name']]) !== null) {
            $this->logger->warning('Duplicate recipe upload skipped', ['name' => $data['name']]);
            return;
        }

        $recipe->setDescription($data['description'] ?? null);
        $recipe->setNeedsApproval(true);

        $this->setMainImage($recipe, $data, $rawImages, $message, $storage);

        foreach ($data['components'] as $componentData) {
            $component = new Component();
            $component->setName($componentData['name'] ?? null);
            $component->setRecipe($recipe);

            foreach ($componentData['ingredients'] as $ingredientData) {
                $ingredientName = $this->ingredientNameRepository->findOrCreate($ingredientData['name']);

                $ingredient = new Ingredient();
                $ingredient->setIngredientName($ingredientName);
                $ingredient->setMeasurement($ingredientData['measurement']);
                $ingredient->setNote($ingredientData['note'] ?? null);
                $component->addIngredient($ingredient);
                $this->entityManager->persist($ingredient);
            }

            $recipe->addComponent($component);
            $this->entityManager->persist($component);
        }

        foreach ($data['steps'] as $stepText) {
            $step = new Step();
            $step->setDetail($stepText);
            $recipe->addStep($step);
            $this->entityManager->persist($step);
        }

        $this->entityManager->persist($recipe);
        $this->entityManager->flush();

        $this->logger->info('AI recipe created, awaiting approval', ['recipe' => $recipe->getName()]);
    }

    private function setMainImage(
        Recipe $recipe,
        array $data,
        array $rawImages,
        ParseRecipeImagesMessage $message,
        FilesystemOperator $storage
    ): void {
        // Set main image from photo_index; crop if bounding box provided
        $photoIndex = $data['photo_index'] ?? $message->photoIndex;
        if ($photoIndex !== null) {
            $zeroIndex = $photoIndex - 1;
            if (isset($message->imageS3Keys[$zeroIndex])) {
                $imageKey = $message->imageS3Keys[$zeroIndex];

                $photoCrop = $data['photo_crop'] ?? null;
                if ($photoCrop !== null) {
                    $cropped = $this->parser->cropImage($rawImages[$zeroIndex], $photoCrop);
                    $imageKey = 'cropped-' . $imageKey;
                    $storage->write($imageKey, $cropped);
                }

                $recipe->setImage($imageKey);
            }
        }
    }
}
