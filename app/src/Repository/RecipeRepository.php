<?php

namespace App\Repository;

use App\Entity\Recipe;
use App\Enum\Course;
use App\Enum\RecipeCategory;
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
     * Find all recipes with optional AND-logic filters by Course and RecipeCategory.
     *
     * @param Course[]          $courses
     * @param RecipeCategory[]  $categories
     * @return Recipe[]
     */
    public function findFiltered(array $courses = [], array $categories = []): array
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.type = :type OR r.type IS NULL')
            ->setParameter('type', RecipeType::RECIPE->value);

        if ($courses !== []) {
            $qb->andWhere('r.course IN (:courses)')
               ->setParameter('courses', array_map(fn(Course $c) => $c->value, $courses));
        }

        // ponytail: LIKE on JSON per value; AND logic via chained andWhere; safe for controlled enum values
        foreach ($categories as $i => $category) {
            $qb->andWhere("r.recipeCategories LIKE :cat{$i}")
               ->setParameter("cat{$i}", '%"' . $category->value . '"%');
        }

        return $qb->orderBy('r.name', 'ASC')->getQuery()->getResult();
    }

    /**
     * @param string[] $ingredientNames
     * @param int[]    $excludeIds
     * @return Recipe[]
     */
    public function search(
        array $ingredientNames = [],
        ?RecipeCategory $recipeCategory = null,
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
        if ($recipeCategory !== null) {
            $qb->andWhere("r.recipeCategories LIKE :category")
               ->setParameter('category', '%"' . $recipeCategory->value . '"%');
        }

        if ($excludeIds !== []) {
            $qb->andWhere('r.id NOT IN (:excludeIds)')->setParameter('excludeIds', $excludeIds);
        }

        return $qb->orderBy('r.name', 'ASC')->getQuery()->getResult();
    }
}
