<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\IngredientName;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class IngredientNameCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return IngredientName::class;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
        ];
    }
}
