<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\IngredientMergeSuggestionRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IngredientMergeSuggestionRepository::class)]
class IngredientMergeSuggestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private IngredientName $nameA;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private IngredientName $nameB;

    #[ORM\Column]
    private DateTimeImmutable $suggestedAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $dismissedAt = null;

    public function __construct(IngredientName $nameA, IngredientName $nameB)
    {
        $this->nameA = $nameA;
        $this->nameB = $nameB;
        $this->suggestedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameA(): IngredientName
    {
        return $this->nameA;
    }

    public function getNameB(): IngredientName
    {
        return $this->nameB;
    }

    public function getSuggestedAt(): DateTimeImmutable
    {
        return $this->suggestedAt;
    }

    public function getDismissedAt(): ?DateTimeImmutable
    {
        return $this->dismissedAt;
    }

    public function dismiss(): void
    {
        $this->dismissedAt = new DateTimeImmutable();
    }
}
