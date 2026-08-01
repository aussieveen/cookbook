<?php

namespace App\Entity;

use App\Enum\IngredientCategory;
use App\Repository\IngredientNameRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: IngredientNameRepository::class)]
class IngredientName
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['recipe:detail'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true, enumType: IngredientCategory::class)]
    #[Groups(['recipe:detail'])]
    private ?IngredientCategory $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): ?IngredientCategory
    {
        return $this->category;
    }

    public function setCategory(?IngredientCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
