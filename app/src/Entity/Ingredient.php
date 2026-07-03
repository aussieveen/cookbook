<?php

namespace App\Entity;

use App\Repository\IngredientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: IngredientRepository::class)]
class Ingredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['recipe:detail'])]
    private ?string $measurement = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['recipe:detail'])]
    private ?string $revisedMeasurement = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['recipe:detail'])]
    private ?string $note = null;

    #[ORM\ManyToOne(inversedBy: 'ingredients')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Component $component = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['recipe:detail'])]
    private ?IngredientName $ingredientName = null;

    #[ORM\Column(nullable: true)]
    private ?float $baseQuantity = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $baseUnit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->ingredientName?->getName();
    }

    public function getMeasurement(): ?string
    {
        return $this->measurement;
    }

    public function setMeasurement(string $measurement): static
    {
        $this->measurement = $measurement;

        return $this;
    }

    public function getRevisedMeasurement(): ?string
    {
        return $this->revisedMeasurement;
    }

    public function setRevisedMeasurement(string $revisedMeasurement): static
    {
        $this->revisedMeasurement = $revisedMeasurement;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getComponent(): ?Component
    {
        return $this->component;
    }

    public function setComponent(?Component $component): static
    {
        $this->component = $component;

        return $this;
    }

    public function getIngredientName(): ?IngredientName
    {
        return $this->ingredientName;
    }

    public function setIngredientName(?IngredientName $ingredientName): static
    {
        $this->ingredientName = $ingredientName;

        return $this;
    }

    public function getBaseQuantity(): ?float
    {
        return $this->baseQuantity;
    }

    public function setBaseQuantity(?float $baseQuantity): static
    {
        $this->baseQuantity = $baseQuantity;

        return $this;
    }

    public function getBaseUnit(): ?string
    {
        return $this->baseUnit;
    }

    public function setBaseUnit(?string $baseUnit): static
    {
        $this->baseUnit = $baseUnit;

        return $this;
    }

    public function __toString(): string
    {
        return trim(($this->ingredientName?->getName() ?? '') . ' ' . ($this->measurement ?? ''));
    }
}
