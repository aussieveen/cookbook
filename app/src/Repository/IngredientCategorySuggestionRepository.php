<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IngredientCategorySuggestion;
use App\Entity\IngredientName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IngredientCategorySuggestion>
 */
class IngredientCategorySuggestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IngredientCategorySuggestion::class);
    }

    public function countPending(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.dismissedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return IngredientCategorySuggestion[] */
    public function findPending(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.dismissedAt IS NULL')
            ->orderBy('s.suggestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function deleteAllPending(): void
    {
        $this->createQueryBuilder('s')
            ->delete()
            ->where('s.dismissedAt IS NULL')
            ->getQuery()
            ->execute();
    }

    public function findPendingForIngredientName(IngredientName $name): ?IngredientCategorySuggestion
    {
        return $this->createQueryBuilder('s')
            ->where('s.ingredientName = :name')
            ->andWhere('s.dismissedAt IS NULL')
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
