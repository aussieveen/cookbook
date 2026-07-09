<?php

namespace App\Repository;

use App\Entity\IngredientName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\ResetInterface;

class IngredientNameRepository extends ServiceEntityRepository implements ResetInterface
{
    /** @var array<string, IngredientName> */
    private array $pendingByName = [];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IngredientName::class);
    }

    public function reset(): void
    {
        $this->pendingByName = [];
    }

    public function findOrCreate(string $name): IngredientName
    {
        $key = mb_strtolower($name);

        if (isset($this->pendingByName[$key])) {
            return $this->pendingByName[$key];
        }

        $existing = $this->createQueryBuilder('i')
            ->where('LOWER(i.name) = LOWER(:name)')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existing !== null) {
            return $this->pendingByName[$key] = $existing;
        }

        $ingredientName = new IngredientName();
        $ingredientName->setName($name);
        $this->getEntityManager()->persist($ingredientName);

        return $this->pendingByName[$key] = $ingredientName;
    }
}
