<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\IngredientName;
use App\Enum\IngredientCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class IngredientNameCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return IngredientName::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Ingredient Name')
            ->setEntityLabelInPlural('Ingredient Names')
            ->setDefaultSort(['name' => 'ASC'])
            ->overrideTemplate('crud/index', 'admin/ingredient_name_index.html.twig')
            ->overrideTemplate('crud/detail', 'admin/ingredient_name_detail.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function configureFields(string $pageName): iterable
    {
        $categoryChoices = [];
        foreach (IngredientCategory::cases() as $case) {
            $categoryChoices[$case->label()] = $case;
        }

        return [
            TextField::new('name'),
            ChoiceField::new('category')
                ->setChoices($categoryChoices)
                ->allowMultipleChoices(false)
                ->renderExpanded(false)
                ->setRequired(false),
        ];
    }
}
