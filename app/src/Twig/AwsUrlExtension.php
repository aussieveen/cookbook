<?php

declare(strict_types=1);

namespace App\Twig;

use App\Service\StorageUrlResolver;
use Generator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AwsUrlExtension extends AbstractExtension
{
    public function __construct(private readonly StorageUrlResolver $resolver)
    {
    }

    public function getFilters(): Generator
    {
        yield new TwigFilter('aws_url', [$this, 'awsUrl']);
    }

    public function awsUrl(string $image): string
    {
        return $this->resolver->getPublicUrl($image);
    }
}
