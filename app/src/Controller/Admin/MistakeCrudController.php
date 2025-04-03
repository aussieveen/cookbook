<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Mistake;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class MistakeCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Mistake::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextareaField::new('description', 'Description'),
            TextareaField::new('fix', 'Fix'),
            AssociationField::new('recipe')
                ->hideOnForm()
        ];
    }
}
