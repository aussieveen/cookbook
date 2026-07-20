<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Ingredient;
use App\Form\IngredientNameType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class IngredientCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Ingredient::class;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function configureFields(string $pageName): iterable
    {
        return [
            Field::new('ingredientName', 'Name')
                ->setFormType(IngredientNameType::class)
                ->setRequired(true),
            TextField::new('measurement', 'Measurement'),
            TextField::new('revisedMeasurement', 'Revised Measurement'),
            TextField::new('note', 'Note'),
            AssociationField::new('recipe')
                ->hideOnForm()
        ];
    }
}
