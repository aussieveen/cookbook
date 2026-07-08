<?php

declare(strict_types=1);

namespace App\Service;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader
{
    public function __construct(
        private readonly FilesystemOperator $activeStorage,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $key = $file->getClientOriginalName();
        $stream = fopen($file->getPathname(), 'r+');

        $this->activeStorage->writeStream($key, $stream);

        return $key;
    }
}
