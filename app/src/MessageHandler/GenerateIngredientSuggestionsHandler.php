<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\GenerateIngredientSuggestionsMessage;
use App\Service\IngredientSuggestionService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GenerateIngredientSuggestionsHandler
{
    public function __construct(private readonly IngredientSuggestionService $service)
    {
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function __invoke(GenerateIngredientSuggestionsMessage $message): void
    {
        $this->service->run();
    }
}
