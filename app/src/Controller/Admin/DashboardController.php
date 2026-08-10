<?php

namespace App\Controller\Admin;

use App\Repository\IngredientCategorySuggestionRepository;
use App\Repository\IngredientMergeSuggestionRepository;
use App\Repository\RecipeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

/** @SuppressWarnings(PHPMD.StaticAccess) */
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly RecipeRepository $recipeRepository,
        private readonly IngredientMergeSuggestionRepository $mergeSuggestionRepository,
        private readonly IngredientCategorySuggestionRepository $categorySuggestionRepository,
    ) {
    }

    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addWebpackEncoreEntry('admin');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Cookbook');
    }

    public function configureMenuItems(): iterable
    {
        $pendingCount = $this->recipeRepository->count(['needsApproval' => true]);
        $mergeSuggestions = $this->mergeSuggestionRepository->countPending();
        $categorySuggestions = $this->categorySuggestionRepository->countPending();

        yield MenuItem::linkToUrl('Dashboard', 'fa fa-home', '/admin');
        yield MenuItem::linkTo(RecipeCrudController::class, 'Recipe', 'fas fa-rectangle-list');

        if ($pendingCount > 0) {
            yield MenuItem::linkTo(
                RecipeCrudController::class,
                sprintf(
                    'Pending Approval (%d)',
                    $pendingCount
                ),
                'fas fa-clock'
            )
                ->setQueryParameter('filters[needsApproval][comparison]', '=')
                ->setQueryParameter('filters[needsApproval][value]', '1');
        }

        $ingredientNamesLabel = $categorySuggestions > 0
            ? sprintf('Ingredient Names (%d)', $categorySuggestions)
            : 'Ingredient Names';
        $mergeNamesLabel = $mergeSuggestions > 0
            ? sprintf('Merge Names (%d)', $mergeSuggestions)
            : 'Merge Names';

        yield MenuItem::linkTo(IngredientNameCrudController::class, $ingredientNamesLabel, 'fas fa-tag');
        yield MenuItem::linkToRoute($mergeNamesLabel, 'fas fa-code-merge', 'admin_ingredient_name_merge');
        yield MenuItem::linkToRoute('Queue Status', 'fa fa-list', 'admin_queue_status');
    }
}
