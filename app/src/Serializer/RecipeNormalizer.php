<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\Recipe;
use App\Service\StorageUrlResolver;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Resolves the stored S3 image key to a full public URL when serializing Recipe entities.
 */
class RecipeNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'RECIPE_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly StorageUrlResolver $urlResolver)
    {
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var array $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        if (isset($data['image']) && is_string($data['image'])) {
            $data['image'] = $this->urlResolver->getPublicUrl($data['image']);
        }

        return $data;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Recipe && !isset($context[self::ALREADY_CALLED]);
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function getSupportedTypes(?string $format): array
    {
        return [Recipe::class => false];
    }
}
