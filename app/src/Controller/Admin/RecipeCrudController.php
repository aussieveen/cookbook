<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Recipe;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class RecipeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextareaField::new('description'),
            ImageField::new('image')
                ->setUploadDir('public/uploads/images')
                ->setBasePath('uploads/images')
                ->setRequired(false)
                ->setLabel('Image'),
            BooleanField::new('mastered'),
            CollectionField::new('components')
                ->useEntryCrudForm(ComponentCrudController::class)
                ->setLabel('Components')
                ->hideOnIndex(),
            CollectionField::new('steps')
                ->useEntryCrudForm(StepCrudController::class)
                ->setLabel('Steps')
                ->hideOnIndex(),
            CollectionField::new('mistakes')
                ->useEntryCrudForm(MistakeCrudController::class)
                ->setLabel('Mistakes')
                ->hideOnIndex(),
        ];
    }
}
