<?php

namespace App\Repository;

use App\Entity\Recipe;
use App\Entity\ShoppingListItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ShoppingListItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShoppingListItem::class);
    }

    public function findByRecipe(Recipe $recipe): ?ShoppingListItem
    {
        return $this->findOneBy(['recipe' => $recipe]);
    }

    /**
     * @return ShoppingListItem[]
     */
    public function findAllWithRecipes(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.recipe', 'r')
            ->addSelect('r')
            ->getQuery()
            ->getResult();
    }
}
