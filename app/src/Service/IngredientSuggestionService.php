<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\IngredientCategorySuggestion;
use App\Entity\IngredientMergeSuggestion;
use App\Entity\IngredientName;
use App\Enum\IngredientCategory;
use App\Repository\IngredientCategorySuggestionRepository;
use App\Repository\IngredientMergeSuggestionRepository;
use App\Repository\IngredientNameRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class IngredientSuggestionService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const MODEL = 'claude-sonnet-4-6';
    private const MAX_TOKENS = 8192;

    public function __construct(
        private readonly HttpClientInterface $client,
        #[Autowire('%env(ANTHROPIC_API_KEY)%')]
        private readonly string $apiKey,
        private readonly IngredientNameRepository $ingredientNameRepository,
        private readonly IngredientMergeSuggestionRepository $mergeSuggestionRepository,
        private readonly IngredientCategorySuggestionRepository $categorySuggestionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function run(): void
    {
        $allNames = $this->ingredientNameRepository->findBy([], ['name' => 'ASC']);

        if (empty($allNames)) {
            return;
        }

        $uncategorised = array_filter($allNames, fn(IngredientName $n) => $n->getCategory() === null);

        $getName = fn(IngredientName $n) => $n->getName();
        $nameList = implode("\n", array_map($getName, $allNames));
        $uncategorisedList = implode("\n", array_map($getName, $uncategorised));
        // phpcs:disable Generic.Files.LineLength.TooLong
        $categories = implode(', ', array_map(fn(IngredientCategory $c) => $c->value . ' (' . $c->label() . ')', IngredientCategory::cases()));
        // phpcs:enable

        $prompt = $this->buildPrompt($nameList, $uncategorisedList, $categories);

        $response = $this->client->request('POST', self::API_URL, [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ],
            'json' => [
                'model' => self::MODEL,
                'max_tokens' => self::MAX_TOKENS,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ],
        ]);

        try {
            $body = $response->toArray();
        } catch (Throwable) {
            throw new RuntimeException('Anthropic error: ' . $response->getContent(false));
        }

        $text = $body['content'][0]['text'] ?? '';
        $data = $this->extractJson($text);

        $nameIndex = [];
        foreach ($allNames as $name) {
            $nameIndex[strtolower((string) $name->getName())] = $name;
        }

        $this->mergeSuggestionRepository->deleteAllPending();
        $this->categorySuggestionRepository->deleteAllPending();

        $this->persistMergeSuggestions($data['merge_pairs'] ?? [], $nameIndex);
        $this->persistCategorySuggestions($data['category_suggestions'] ?? [], $nameIndex);

        $this->entityManager->flush();
    }

    /** @param array<int,array<string,string>> $pairs @param array<string,IngredientName> $nameIndex */
    private function persistMergeSuggestions(array $pairs, array $nameIndex): void
    {
        foreach ($pairs as $pair) {
            $a = $nameIndex[strtolower($pair['a'] ?? '')] ?? null;
            $b = $nameIndex[strtolower($pair['b'] ?? '')] ?? null;

            if ($a === null || $b === null || $a === $b) {
                continue;
            }

            $this->entityManager->persist(new IngredientMergeSuggestion($a, $b));
        }
    }

    /** @param array<int,array<string,string>> $suggestions @param array<string,IngredientName> $nameIndex */
    private function persistCategorySuggestions(array $suggestions, array $nameIndex): void
    {
        foreach ($suggestions as $suggestion) {
            $name = $nameIndex[strtolower($suggestion['name'] ?? '')] ?? null;
            $category = IngredientCategory::tryFrom($suggestion['category'] ?? '');

            if ($name === null || $category === null) {
                continue;
            }

            $this->entityManager->persist(new IngredientCategorySuggestion($name, $category));
        }
    }

    // phpcs:disable
    private function buildPrompt(string $nameList, string $uncategorisedList, string $categories): string
    {
        return <<<PROMPT
You are helping manage a recipe ingredient database. Return ONLY a JSON object — no markdown, no explanation.

TASK 1 — MERGE PAIRS
Here is the full list of ingredient names:
{$nameList}

Identify pairs that clearly refer to the same ingredient and should be merged (e.g. "Water For The Gravy" and "Water For The Ragu" are both just water). Only suggest high-confidence pairs.

In each pair, "a" must be the MORE specific or complex name (e.g. "Oil for the Breadcrumbs", "Water For The Gravy") and "b" must be the simpler, canonical name it should be merged INTO (e.g. "Oil", "Water"). The merge will replace "a" with "b".

If multiple specific names all refer to the same canonical ingredient (e.g. "Sugar For The Pickle", "Sugar For The Dressing", "Sugar for the Onions" all map to "Sugar"), every one of them must have "b" set to that single canonical name — do NOT chain them to each other.

TASK 2 — CATEGORY SUGGESTIONS
Here are ingredient names that currently have no category:
{$uncategorisedList}

Assign each one to the most appropriate category from this list: {$categories}

Return this exact JSON structure:
{
  "merge_pairs": [
    {"a": "exact name from list", "b": "exact name from list"}
  ],
  "category_suggestions": [
    {"name": "exact name from uncategorised list", "category": "category_value"}
  ]
}
PROMPT;
    }
    // phpcs:enable

    private function extractJson(string $text): array
    {
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/^```\s*$/m', '', $text);

        $data = json_decode(trim($text), true);

        if (!is_array($data)) {
            throw new RuntimeException('Claude returned unexpected JSON: ' . $text);
        }

        return $data;
    }
}
