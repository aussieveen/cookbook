<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\IngredientCategory;
use App\Repository\IngredientCategorySuggestionRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IngredientCategorySuggestionRepository::class)]
class IngredientCategorySuggestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private IngredientName $ingredientName;

    #[ORM\Column(enumType: IngredientCategory::class)]
    private IngredientCategory $suggestedCategory;

    #[ORM\Column]
    private DateTimeImmutable $suggestedAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $dismissedAt = null;

    public function __construct(IngredientName $ingredientName, IngredientCategory $suggestedCategory)
    {
        $this->ingredientName = $ingredientName;
        $this->suggestedCategory = $suggestedCategory;
        $this->suggestedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIngredientName(): IngredientName
    {
        return $this->ingredientName;
    }

    public function getSuggestedCategory(): IngredientCategory
    {
        return $this->suggestedCategory;
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
