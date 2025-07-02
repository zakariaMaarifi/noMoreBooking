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
use App\Repository\PartnerRepository;
use App\Repository\MessageRepository;
use App\Entity\Hotel;
use App\Entity\Reservation;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\ReservationTypeForm;
use App\Form\HotelFilterType;

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
    public function offres(Request $request, HotelRepository $hotelRepository): Response
    {
        // Crée le formulaire de filtre
        $form = $this->createForm(HotelFilterType::class);
        $form->handleRequest($request);

        $filters = $form->getData() ?? []; // <-- Ajoute cette ligne

        // Récupère les hôtels filtrés par ville/date via une méthode personnalisée
        $hotels = $hotelRepository->findWithFilters($filters);

        // dd($hotels);

        // Ne garde que les hôtels avec au moins une réservation disponible
        // $hotelsWithAvailable = array_filter($hotels, function ($hotel) use ($filters) {
        //     foreach ($hotel->getReservations() as $reservation) {
        //         $available = $reservation->isAvailable();

        //         // Si un filtre date est appliqué, on vérifie que la réservation correspond à cette date
        //         if ($available && (!isset($filters['date']) || $filters['date'] === null || $reservation->getDate() == $filters['date'])) {
        //             return true;
        //         }
        //     }
        //     return false;
        // });

        return $this->render('client/offres.html.twig', [
            'form' => $form->createView(),
            'hotels' => $hotels,
            'hasSearched' => $form->isSubmitted(),
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

    #[Route('/client/hotel/{id}/reservations', name: 'client_hotel_reservations')]
    public function hotelReservations(Hotel $hotel): Response
    {
        $availableReservations = [];
        foreach ($hotel->getReservations() as $reservation) {
            if ($reservation->isAvailable()) {
                $availableReservations[] = $reservation;
            }
        }

        return $this->render('client/hotel_reservations.html.twig', [
            'hotel' => $hotel,
            'reservations' => $availableReservations,
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

    #[Route('/client/reserver-reservation/{id}', name: 'client_reserver_reservation')]
    public function reserverReservation(
        Reservation $reservation,
        EntityManagerInterface $entityManager
    ): Response {
        $client = $this->getUser();

        if (!$reservation->isAvailable()) {
            $this->addFlash('error', 'Cette réservation n\'est plus disponible.');
            return $this->redirectToRoute('client_hotel_reservations', ['id' => $reservation->getHotel()->getId()]);
        }

        $reservation->setClient($client);
        $reservation->setIsAvailable(false);
        $entityManager->flush();

        $this->addFlash('success', 'Réservation effectuée avec succès !');
        return $this->redirectToRoute('client_reservations');
    }
    #[Route('/client/annuler-reservation/{id}', name: 'client_annuler_reservation', methods: ['POST'])]
    public function annulerReservation(
        Reservation $reservation,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $client = $this->getUser();

        // Vérifie que la réservation appartient bien au client
        if ($reservation->getClient() !== $client) {
            throw $this->createAccessDeniedException();
        }

        // Vérifie le token CSRF
        if ($this->isCsrfTokenValid('annuler' . $reservation->getId(), $request->request->get('_token'))) {
            $reservation->setClient(null);
            $reservation->setIsAvailable(true);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation annulée avec succès.');
        }

        return $this->redirectToRoute('client_reservations');
    }
    #[Route('/client/message-partner/{partnerId}', name: 'client_message_partner', methods: ['POST'])]
    public function messagePartner(
        int $partnerId,
        Request $request,
        EntityManagerInterface $entityManager,
        PartnerRepository $partnerRepository
    ): Response {
        $client = $this->getUser();
        $partner = $partnerRepository->find($partnerId);

        $content = $request->request->get('content');
        if ($partner && $content) {
            $message = new Message();
            $message->setSender($client);
            $message->setReceiver($partner);
            $message->setContent($content);
            $message->setSentAt(new \DateTime());
            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Message envoyé au partenaire.');
        }

        return $this->redirect($request->headers->get('referer'));
    }
    #[Route('/client/discussion-partner/{partnerId}', name: 'client_discussion_partner')]
    public function discussionPartner(
        int $partnerId,
        PartnerRepository $partnerRepository,
        MessageRepository $messageRepository
    ): Response {
        $client = $this->getUser();
        $partner = $partnerRepository->find($partnerId);

        // Récupère tous les messages entre ce client et ce partenaire
        $messages = $messageRepository->createQueryBuilder('m')
            ->where('(m.sender = :client AND m.receiver = :partner) OR (m.sender = :partner AND m.receiver = :client)')
            ->setParameter('client', $client)
            ->setParameter('partner', $partner)
            ->orderBy('m.sentAt', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('client/discussion_partner.html.twig', [
            'partner' => $partner,
            'messages' => $messages,
        ]);
    }
}
