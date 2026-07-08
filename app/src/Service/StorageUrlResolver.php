<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class StorageUrlResolver
{
    public function __construct(
        #[Autowire('%s3.bucket.name%')]
        private readonly ?string $bucket,
        #[Autowire('%s3.bucket.region%')]
        private readonly ?string $region,
    ) {
    }

    public function isS3(): bool
    {
        return !empty($this->bucket);
    }

    public function getPublicUrl(string $key): string
    {
        if ($this->isS3()) {
            return sprintf('https://%s.s3.%s.amazonaws.com/images/%s', $this->bucket, $this->region, $key);
        }

        return '/uploads/images/' . $key;
    }

    /** Base URL for EasyAdmin ImageField::setBasePath() */
    public function getBaseUrl(): string
    {
        if ($this->isS3()) {
            return sprintf('https://%s.s3.%s.amazonaws.com/images/', $this->bucket, $this->region);
        }

        return '/uploads/images/';
    }
}
