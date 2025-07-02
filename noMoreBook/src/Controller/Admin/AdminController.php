<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Client;
use App\Entity\User;
use App\Entity\Partner;
use App\Entity\Hotel;
use App\Entity\Reservation;

#[AdminDashboard(routePath: '/admincrud', routeName: 'admin')]
class AdminController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(\EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator::class);
    return $this->redirect($adminUrlGenerator->setController(\App\Controller\Admin\ClientCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('NoMoreBook');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
        yield MenuItem::linkToCrud('Clients', 'fas fa-users', Client::class);
        yield MenuItem::linkToCrud('Partenaires', 'fas fa-users', Partner::class);
        yield MenuItem::linkToCrud('Admins', 'fas fa-user-tie', Admin::class);
        yield MenuItem::linkToCrud('Hôtels', 'fas fa-hotel', Hotel::class);
        yield MenuItem::linkToCrud('Réservations', 'fas fa-calendar', Reservation::class);
    }
}
