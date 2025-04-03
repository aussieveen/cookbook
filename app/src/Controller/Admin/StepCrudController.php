<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Step;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class StepCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Step::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextareaField::new('detail'),
            AssociationField::new('recipe')
                ->hideOnForm()
        ];
    }
}
