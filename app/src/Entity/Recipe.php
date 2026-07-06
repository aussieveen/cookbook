<?php

namespace App\Entity;

use App\Enum\Course;
use App\Enum\MealOccasion;
use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Recipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['recipe:summary', 'recipe:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['recipe:summary', 'recipe:detail'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['recipe:summary', 'recipe:detail'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['recipe:detail'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['recipe:detail'])]
    private ?string $image = null;

    /**
     * @var Collection<int, Step>
     */
    #[ORM\OneToMany(targetEntity: Step::class, mappedBy: 'recipe', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['recipe:detail'])]
    private Collection $steps;

    /**
     * @var Collection<int, Mistake>
     */
    #[ORM\OneToMany(targetEntity: Mistake::class, mappedBy: 'recipe', cascade: ['persist'], orphanRemoval: true)]
    private Collection $mistakes;

    #[ORM\Column(nullable: true)]
    #[Groups(['recipe:summary', 'recipe:detail'])]
    private ?bool $mastered = null;

    #[ORM\Column(length: 20, nullable: true, enumType: Course::class)]
    #[Groups(['recipe:summary', 'recipe:detail'])]
    private ?Course $course = null;

    /** @var string[] */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['recipe:summary', 'recipe:detail'])]
    private array $mealOccasions = [];

    /**
     * @var Collection<int, Component>
     */
    #[ORM\OneToMany(targetEntity: Component::class, mappedBy: 'recipe', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['recipe:detail'])]
    private Collection $components;

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\ManyToMany(targetEntity: Recipe::class, inversedBy: 'pairedBy')]
    #[ORM\JoinTable(
        name: 'recipe_pairing',
        joinColumns: [new ORM\JoinColumn(name: 'recipe_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'paired_recipe_id', referencedColumnName: 'id')]
    )]
    #[Groups(['recipe:detail'])]
    private Collection $pairsWith;

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\ManyToMany(targetEntity: Recipe::class, mappedBy: 'pairsWith')]
    private Collection $pairedBy;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
        $this->mistakes = new ArrayCollection();
        $this->components = new ArrayCollection();
        $this->pairsWith = new ArrayCollection();
        $this->pairedBy = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
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

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function generateSlug(LifecycleEventArgs $eventArgs): void
    {
        if (!$this->slug && $this->name) {
            $this->slug = strtolower(str_replace(' ', '-', $this->name));
        }
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        if ($image !== null) {
            $this->image = $image;
        }

        return $this;
    }

    /**
     * @return Collection<int, Step>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(Step $step): static
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
            $step->setRecipe($this);
        }

        return $this;
    }

    public function removeStep(Step $step): static
    {
        if ($this->steps->removeElement($step)) {
            // set the owning side to null (unless already changed)
            if ($step->getRecipe() === $this) {
                $step->setRecipe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Mistake>
     */
    public function getMistakes(): Collection
    {
        return $this->mistakes;
    }

    public function addMistake(Mistake $mistake): static
    {
        if (!$this->mistakes->contains($mistake)) {
            $this->mistakes->add($mistake);
            $mistake->setRecipe($this);
        }

        return $this;
    }

    public function removeMistake(Mistake $mistake): static
    {
        if ($this->mistakes->removeElement($mistake)) {
            // set the owning side to null (unless already changed)
            if ($mistake->getRecipe() === $this) {
                $mistake->setRecipe(null);
            }
        }

        return $this;
    }

    public function isMastered(): ?bool
    {
        return $this->mastered;
    }

    public function setMastered(?bool $mastered): static
    {
        $this->mastered = $mastered;

        return $this;
    }

    /**
     * @return Collection<int, Component>
     */
    public function getComponents(): Collection
    {
        return $this->components;
    }

    public function addComponent(Component $component): static
    {
        if (!$this->components->contains($component)) {
            $this->components->add($component);
            $component->setRecipe($this);
        }

        return $this;
    }

    public function removeComponent(Component $component): static
    {
        if ($this->components->removeElement($component)) {
            // set the owning side to null (unless already changed)
            if ($component->getRecipe() === $this) {
                $component->setRecipe(null);
            }
        }

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;

        return $this;
    }

    /** @return MealOccasion[] */
    /** @SuppressWarnings(PHPMD.StaticAccess) ponytail: BackedEnum::from() has no non-static equivalent */
    public function getMealOccasions(): array
    {
        return array_map(MealOccasion::from(...), $this->mealOccasions);
    }

    /** @param MealOccasion[] $mealOccasions */
    public function setMealOccasions(array $mealOccasions): static
    {
        $this->mealOccasions = array_map(fn(MealOccasion $o) => $o->value, $mealOccasions);

        return $this;
    }

    /** @return Collection<int, Recipe> */
    public function getPairsWith(): Collection
    {
        return $this->pairsWith;
    }

    public function addPairsWith(Recipe $recipe): static
    {
        if (!$this->pairsWith->contains($recipe)) {
            $this->pairsWith->add($recipe);
        }

        return $this;
    }

    public function removePairsWith(Recipe $recipe): static
    {
        $this->pairsWith->removeElement($recipe);

        return $this;
    }

    /** @return Collection<int, Recipe> */
    public function getPairedBy(): Collection
    {
        return $this->pairedBy;
    }

    /** @return Collection<int, Recipe> All pairings regardless of which side initiated */
    public function getAllPairings(): Collection
    {
        $unique = [];
        foreach (array_merge($this->pairsWith->toArray(), $this->pairedBy->toArray()) as $r) {
            $unique[spl_object_id($r)] = $r;
        }

        return new ArrayCollection(array_values($unique));
    }
}
