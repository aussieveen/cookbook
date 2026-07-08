<?php

declare(strict_types=1);

namespace App\Service;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader
{
    public function __construct(
        private readonly FilesystemOperator $s3Storage,
        private readonly FilesystemOperator $defaultStorage,
        private readonly StorageUrlResolver $resolver,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $key = $file->getClientOriginalName();
        $stream = fopen($file->getPathname(), 'r+');

        $this->storage()->writeStream($key, $stream);

        return $key;
    }

    private function storage(): FilesystemOperator
    {
        return $this->resolver->isS3() ? $this->s3Storage : $this->defaultStorage;
    }
}
