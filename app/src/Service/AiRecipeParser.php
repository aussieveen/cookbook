<?php

declare(strict_types=1);

namespace App\Service;

use RuntimeException;
use Runtimexception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class AiRecipeParser
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const MODEL = 'claude-sonnet-4-6';
    private const MAX_TOKENS = 4096;
    private const MAX_SIDE_PX = 2048;
    private const MAX_BYTES_RAW = 3_000_000;

    public function __construct(
        private readonly HttpClientInterface $client,
        #[Autowire('%env(ANTHROPIC_API_KEY)%')]
        private readonly string $apiKey,
    ) {
    }

    /**
     * Parse recipe images via Claude vision.
     *
     * @param string[] $base64Images Base64-encoded image data (JPEG/PNG)
     * @return array{
     *     name: string,
     *     description: string,
     *     components: list<array{
     *         name: ?string,
     *         ingredients: list<array{
     *             name: string,
     *             measurement: string,
     *             note: ?string
     *         }>
     *     }>,
     *     steps: list<string>,
     *     photo_index: int|null,
     *     photo_crop: array{x_pct: float, y_pct: float, width_pct: float, height_pct: float}|null}
     */
    public function parse(array $base64Images): array
    {
        $content = $this->buildContent($base64Images);

        $response = $this->client->request('POST', self::API_URL, [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ],
            'json' => [
                'model' => self::MODEL,
                'max_tokens' => self::MAX_TOKENS,
                'messages' => [['role' => 'user', 'content' => $content]],
            ],
        ]);

        try {
            $body = $response->toArray();
        } catch (Throwable $e) {
            throw new RuntimeException("Anthropic error: " . $response->getContent(false));
        }
        $text = $body['content'][0]['text'] ?? '';

        return $this->extractJson($text);
    }

    /**
     * Crop a JPEG image using percentage-based bounding box coords.
     *
     * @param array{x_pct: float, y_pct: float, width_pct: float, height_pct: float} $crop
     */
    public function cropImage(string $rawJpeg, array $crop): string
    {
        // Resize first to avoid OOM on large originals
        $rawJpeg = $this->resizeForApi($rawJpeg);

        $src = imagecreatefromstring($rawJpeg);
        if ($src === false) {
            return $rawJpeg;
        }

        $w = imagesx($src);
        $h = imagesy($src);

        $x = (int) round($w * $crop['x_pct'] / 100);
        $y = (int) round($h * $crop['y_pct'] / 100);
        $cw = (int) round($w * $crop['width_pct'] / 100);
        $ch = (int) round($h * $crop['height_pct'] / 100);

        // Guard against degenerate crops
        $cw = max(1, min($cw, $w - $x));
        $ch = max(1, min($ch, $h - $y));

        $dst = imagecreatetruecolor($cw, $ch);
        imagecopy($dst, $src, 0, 0, $x, $y, $cw, $ch);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, 90);
        $result = ob_get_clean();
        imagedestroy($dst);

        return $result ?: $rawJpeg;
    }

    /** @param string[] $base64Images */
    private function buildContent(array $base64Images): array
    {
        $content = [];

        foreach ($base64Images as $index => $base64) {
            $resized = $this->resizeForApi(base64_decode($base64));
            $content[] = [
                'type' => 'image',
                'source' => [
                    'type' => 'base64',
                    'media_type' => 'image/jpeg',
                    'data' => base64_encode($resized),
                ],
            ];
            $content[] = ['type' => 'text', 'text' => "Image " . ($index + 1) . " of " . count($base64Images)];
        }

        $content[] = ['type' => 'text', 'text' => $this->systemPrompt(count($base64Images))];

        return $content;
    }
    // phpcs:disable
    private function systemPrompt(int $imageCount): string
    {
        return <<<PROMPT
You are parsing recipe images to extract a structured recipe.

Rules:
1. If the recipe lists quantities for multiple serving sizes (e.g. 2, 3, or 4 people), ALWAYS use the quantities for the MAXIMUM serving size.
2. Group ingredients by component/section if the recipe has sections (e.g. "For the sauce", "For the dough"). If there are no sections, use a single component with a null name.
3. Return ONLY a JSON object — no markdown, no explanation, just the raw JSON.
4. If any of the {$imageCount} image(s) appears to be a food/dish photograph rather than text/ingredient content, set photo_index to its 1-based index. Otherwise set photo_index to null.
5. When photo_index is set, also set photo_crop to the tightest bounding box around ONLY the food/dish photograph in that image, expressed as percentages (0-100) of the image dimensions. Aggressively exclude: header bars, text overlays, ingredient panels, nutrition labels, logos, watermarks, cutlery, decorative borders, and any non-photographic UI elements — even if they are at the edges of an otherwise photographic image. If the food genuinely fills the entire frame with no UI chrome, use {"x_pct":0,"y_pct":0,"width_pct":100,"height_pct":100}. Set photo_crop to null when photo_index is null.
6. Group steps by the subject being worked on — all actions focused on the same ingredient or component belong in one step (e.g. everything for making mash, or all prep for one vegetable). Start a new step when the cook switches focus to a different ingredient or component, including when "meanwhile" or similar introduces work on something different. A step may contain multiple sentences if they all concern the same subject. Do not split sentences that are part of the same continuous task; do not keep sentences together that are working on different subjects.
7. For ingredients, put ONLY the ingredient name in "name" and ONLY the quantity/unit in "measurement". Any annotation such as "optional", "to serve", "to garnish", "for the sauce", "for the dressing", or similar qualifiers must go in the "note" field (string or null). Do not embed these annotations in "name" or "measurement".

Return this exact structure:
{
  "name": "Recipe name",
  "description": "Brief description or null",
  "components": [
    {
      "name": null,
      "ingredients": [
        {"name": "ingredient name", "measurement": "quantity and unit", "note": null}
      ]
    }
  ],
  "steps": ["Step 1 text", "Step 2 text"],
  "photo_index": null,
  "photo_crop": null
}
PROMPT;
    }
    // phpcs:disable

    /**
     * Resize image to fit Claude API limits (~3MB raw / 2048px max side).
     * Converts to JPEG for consistent handling.
     * ponytail: GD only; fine for recipe card photos
     */
    private function resizeForApi(string $rawImageData): string
    {
        $src = imagecreatefromstring($rawImageData);
        if ($src === false) {
            return $rawImageData;
        }

        $w = imagesx($src);
        $h = imagesy($src);

        if ($w <= self::MAX_SIDE_PX && $h <= self::MAX_SIDE_PX && strlen($rawImageData) <= self::MAX_BYTES_RAW) {
            imagedestroy($src);
            // Re-encode as JPEG for consistent media_type
            ob_start();
            imagejpeg($src, null, 90);
            return ob_get_clean() ?: $rawImageData;
        }

        $scale = min(self::MAX_SIDE_PX / $w, self::MAX_SIDE_PX / $h, 1.0);
        $newW = (int) round($w * $scale);
        $newH = (int) round($h * $scale);

        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, 85);
        $resized = ob_get_clean();
        imagedestroy($dst);

        return $resized ?: $rawImageData;
    }

    private function extractJson(string $text): array
    {
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/^```\s*$/m', '', $text);

        $data = json_decode(trim($text), true);

        if (!is_array($data) || !isset($data['name'], $data['steps'], $data['components'])) {
            throw new RuntimeException('Claude returned unexpected JSON structure: ' . $text);
        }

        return $data;
    }
}
