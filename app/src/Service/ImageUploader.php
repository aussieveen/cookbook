<?php

declare(strict_types=1);

namespace App\Service;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader
{
    public function __construct(private readonly FilesystemOperator $s3Storage)
    {
    }

    public function upload(UploadedFile $file): string
    {
        $this->s3Storage->writeStream($file->getClientOriginalName(), fopen($file->getPathname(), 'r+'));

        return $file->getClientOriginalName();
    }
}
