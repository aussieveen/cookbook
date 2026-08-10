<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IngredientMergeSuggestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IngredientMergeSuggestion>
 */
class IngredientMergeSuggestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IngredientMergeSuggestion::class);
    }

    public function countPending(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.dismissedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return IngredientMergeSuggestion[] */
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
}
