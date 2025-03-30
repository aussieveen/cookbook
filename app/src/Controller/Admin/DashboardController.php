<?php

namespace App\Controller\Admin;

use App\Entity;
use App\Repository\ZoneRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/', routeName: 'home')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(

    ) {
    }

//    public function index(): Response
//    {
//        return $this->render('admin/dashboard.html.twig', [
//            'zones' => $this->zoneRepository->findAll()
//        ]);
//    }
//
//    /** @SuppressWarnings(PHPMD.StaticAccess) */
//    public function configureDashboard(): Dashboard
//    {
//        return Dashboard::new()
//            ->setTitle('Inventory management')
//            ->setDefaultColorScheme('dark');
//    }
//
//    /** @SuppressWarnings(PHPMD.StaticAccess) */
//    public function configureMenuItems(): iterable
//    {
//        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
//        yield MenuItem::linkToCrud('Items', 'fas fa-qrcode', Entity\Item::class);
//        yield MenuItem::linkToCrud('Containers', 'fas fa-box', Entity\Container::class);
//        yield MenuItem::linkToCrud('Zones', 'fas fa-warehouse', Entity\Zone::class);
//        yield MenuItem::linkToCrud('Categories', 'fas fa-list', Entity\Category::class);
//    }
}
