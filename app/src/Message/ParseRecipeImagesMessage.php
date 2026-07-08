<?php

declare(strict_types=1);

namespace App\Message;

final class ParseRecipeImagesMessage
{
    /** @param string[] $imageS3Keys */
    public function __construct(
        public readonly array $imageS3Keys,
        public readonly ?int $photoIndex = null,
    ) {
    }
}
