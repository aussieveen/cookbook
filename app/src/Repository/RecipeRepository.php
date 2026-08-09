<?php

namespace App\Repository;

use App\Entity\Recipe;
use App\Enum\Course;
use App\Enum\MealOccasion;
use App\Enum\RecipeType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    public function save(Recipe $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /** @return Recipe[] */
    public function findAllRecipes(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.type = :type OR r.type IS NULL')
            ->setParameter('type', RecipeType::RECIPE->value)
            ->orderBy('r.name', 'ASC')
            ->getQuery()->getResult();
    }

    /**
     * Search recipes by optional filters.
     * @param string[] $ingredientNames
     * @return Recipe[]
     */
    /**
     * @param string[] $ingredientNames
     * @param int[]    $excludeIds
     * @return Recipe[]
     */
    public function search(
        array $ingredientNames = [],
        ?MealOccasion $mealOccasion = null,
        ?Course $course = null,
        ?string $nameQuery = null,
        array $excludeIds = [],
    ): array {
        $qb = $this->createQueryBuilder('r');

        if ($ingredientNames !== []) {
            $qb->join('r.components', 'comp')
               ->join('comp.ingredients', 'ing')
               ->join('ing.ingredientName', 'iname');

            foreach ($ingredientNames as $i => $name) {
                $qb->andWhere("LOWER(iname.name) LIKE LOWER(:ing{$i})")
                   ->setParameter("ing{$i}", '%' . $name . '%');
            }

            $qb->distinct();
        }

        if ($nameQuery !== null && $nameQuery !== '') {
            $qb->andWhere('LOWER(r.name) LIKE LOWER(:nameQuery)')
               ->setParameter('nameQuery', '%' . $nameQuery . '%');
        }

        if ($course !== null) {
            $qb->andWhere('r.course = :course')->setParameter('course', $course->value);
        }

        // ponytail: LIKE on JSON; safe for controlled enum values, avoids custom DQL function registration
        if ($mealOccasion !== null) {
            $qb->andWhere("r.mealOccasions LIKE :occasion")
               ->setParameter('occasion', '%"' . $mealOccasion->value . '"%');
        }

        if ($excludeIds !== []) {
            $qb->andWhere('r.id NOT IN (:excludeIds)')->setParameter('excludeIds', $excludeIds);
        }

        return $qb->orderBy('r.name', 'ASC')->getQuery()->getResult();
    }
}
