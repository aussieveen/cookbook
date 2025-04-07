<?php

declare(strict_types=1);

namespace App\Twig;

use Generator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AwsUrlExtension extends AbstractExtension
{
    public function __construct(
        #[Autowire('%s3.bucket.name%')]
        private string $s3BucketName,
        #[Autowire('%s3.bucket.region%')]
        private string $s3Region,
    ) {
    }

    public function getFilters(): Generator
    {
        yield new TwigFilter('aws_url', [$this, 'awsUrl']);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function awsUrl(string $image): string
    {
        return sprintf(
            'https://%s.s3.%s.amazonaws.com/images/%s',
            $this->s3BucketName,
            $this->s3Region,
            $image
        );
    }
}
