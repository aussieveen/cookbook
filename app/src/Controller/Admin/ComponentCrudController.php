<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Component;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class ComponentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Component::class;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Name'),
            CollectionField::new('ingredients')
                ->useEntryCrudForm(IngredientCrudController::class)
                ->setLabel('Ingredients')
                ->hideOnIndex(),
        ];
    }
}
