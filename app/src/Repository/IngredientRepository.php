<?php

namespace App\Repository;

use App\Entity\Ingredient;
use App\Entity\IngredientName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ingredient>
 */
class IngredientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ingredient::class);
    }

    /**
     * Return all Ingredient rows that reference the given IngredientName,
     * with their component and recipe eagerly loaded for display.
     *
     * @return Ingredient[]
     */
    public function findByIngredientName(IngredientName $ingredientName): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.component', 'c')
            ->join('c.recipe', 'r')
            ->addSelect('c', 'r')
            ->where('i.ingredientName = :name')
            ->setParameter('name', $ingredientName)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
