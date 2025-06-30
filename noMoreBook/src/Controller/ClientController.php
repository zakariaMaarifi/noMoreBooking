<?php

// namespace App\Controller;

// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Routing\Attribute\Route;

// final class ClientController extends AbstractController
// {
//     #[Route('/client', name: 'app_client')]
//     public function index(): Response
//     {
//         return $this->render('client/index.html.twig', [
//             'controller_name' => 'ClientController',
//         ]);
//     }

//     #[Route('/client/dashboard', name: 'client_dashboard')]
//     public function dashboard(): Response
//     {
//         return $this->render('client/dashboard.html.twig', [
//             'controller_name' => 'ClientController',
//         ]);
//     }
// }

namespace App\Controller;

use App\Repository\HotelRepository;
use App\Repository\ReservationRepository;
use App\Entity\Hotel;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\ReservationTypeForm;

#[IsGranted('ROLE_CLIENT')]
final class ClientController extends AbstractController
{
    #[Route('/client', name: 'app_client')]
    public function index(): Response
    {
        return $this->redirectToRoute('client_dashboard');
    }

    #[Route('/client/dashboard', name: 'client_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('client/dashboard.html.twig');
    }

    #[Route('/client/offres', name: 'client_offers')]
    public function offres(HotelRepository $hotelRepository): Response
    {
        $offres = $hotelRepository->findAvailableOffers();
        return $this->render('client/offres.html.twig', [
            'offres' => $offres,
        ]);
    }
    #[Route('/client/offre/{id}', name: 'client_offer_detail')]
public function offerDetail(Hotel $hotel): Response
{
    return $this->render('client/offer_detail.html.twig', [
        'hotel' => $hotel,
    ]);
}

    #[Route('/client/reservations', name: 'client_reservations')]
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        $client = $this->getUser();
        $reservations = $reservationRepository->findBy(['client' => $client]);

        return $this->render('client/reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/client/reserver/{id}', name: 'client_reserver_hotel')]
    public function reserverHotel(
         Hotel $hotel,
    EntityManagerInterface $entityManager,
    Request $request
): Response {
    $client = $this->getUser();
    $reservation = new Reservation();
    $reservation->setHotel($hotel);
    $reservation->setClient($client);

    $form = $this->createForm(ReservationTypeForm::class, $reservation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Réservation effectuée avec succès !');
        return $this->redirectToRoute('client_reservations');
    }

    return $this->render('client/reserver.html.twig', [
        'reservationForm' => $form->createView(),
        'hotel' => $hotel,
    ]);
    }
}

