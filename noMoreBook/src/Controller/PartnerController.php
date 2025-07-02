<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Reservation;
use App\Entity\Hotel;
use App\Entity\Client;
use App\Entity\Message;
use App\Form\ReservationType;
use App\Repository\MessageRepository;
use App\Repository\ClientRepository;

final class PartnerController extends AbstractController
{
    #[Route('/partner', name: 'app_partner')]
    public function index(): Response
    {
        return $this->render('partner/index.html.twig', [
            'controller_name' => 'PartnerController',
        ]);
    }
    // #[Route('/partner/dashboard', name: 'partner_dashboard')]
    // public function dashboard(EntityManagerInterface $em): Response
    // {
    //     $partner = $this->getUser();
    //     $reservations = $em->getRepository(Reservation::class)->findBy(['Partner' => $partner]);

    //     return $this->render('partner/dashboard.html.twig', [
    //         'reservations' => $reservations,
    //     ]);
    // }
    #[Route('/partner/dashboard', name: 'partner_dashboard')]
public function dashboard(EntityManagerInterface $em): Response
{
    $partner = $this->getUser();

    // Réservations créées par le partenaire mais PAS encore réservées par un client
    $disponibles = $em->getRepository(Reservation::class)->findBy([
        'Partner' => $partner,
        'client' => null,
    ]);

    // Réservations créées par le partenaire ET réservées par un client
    $reservees = $em->getRepository(Reservation::class)->createQueryBuilder('r')
        ->where('r.Partner = :partner')
        ->andWhere('r.client IS NOT NULL')
        ->setParameter('partner', $partner)
        ->getQuery()
        ->getResult();


    return $this->render('partner/dashboard.html.twig', [
        'disponibles' => $disponibles,
        'reservees' => $reservees,
    ]);
}
    #[Route('/partner/reservation/new', name: 'partner_reservation_new')]
public function newReservation(Request $request, EntityManagerInterface $em): Response
{
    $partner = $this->getUser();
    $reservation = new Reservation();
    $reservation->setPartner($partner);

    $form = $this->createForm(ReservationType::class, $reservation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $hotelName = $form->get('hotelName')->getData();
        $hotelCity = $form->get('hotelCity')->getData();

        // Vérifie si l'hôtel existe déjà pour ce partenaire
        $hotel = $em->getRepository(Hotel::class)->findOneBy([
            'name' => $hotelName,
            'city' => $hotelCity,
            'partner' => $partner,
        ]);

        if (!$hotel) {
            $hotel = new Hotel();
            $hotel->setName($hotelName);
            $hotel->setCity($hotelCity);
            $hotel->setPartner($partner);
            $hotel->setStatus('available');
            $em->persist($hotel);
        }

        $reservation->setHotel($hotel);
        $em->persist($reservation);
        $em->flush();

        $this->addFlash('success', 'Réservation et hôtel créés ou associés !');
        return $this->redirectToRoute('partner_dashboard');
    }

    return $this->render('partner/reservation_form.html.twig', [
        'form' => $form->createView(),
    ]);
}
    #[Route('/partner/reservation/{id}/edit', name: 'partner_reservation_edit')]
    public function editReservation(Reservation $reservation, Request $request, EntityManagerInterface $em): Response
    {
        $partner = $this->getUser();
        if ($reservation->getPartner() !== $partner) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->get('hotelName')->setData($reservation->getHotel()->getName());
        $form->get('hotelCity')->setData($reservation->getHotel()->getCity());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        $hotelName = $form->get('hotelName')->getData();
        $hotelCity = $form->get('hotelCity')->getData();
        $hotel = $reservation->getHotel();
        $hotel->setName($hotelName);
        $hotel->setCity($hotelCity);

        $em->flush();
        $this->addFlash('success', 'Réservation modifiée !');
        return $this->redirectToRoute('partner_dashboard');
    }

        return $this->render('partner/edit_reservation.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/partner/reservation/{id}/delete', name: 'partner_reservation_delete', methods: ['POST'])]
    public function deleteReservation(Reservation $reservation, EntityManagerInterface $em, Request $request): Response
    {
        $partner = $this->getUser();
        if ($reservation->getPartner() !== $partner) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $em->remove($reservation);
            $em->flush();
            $this->addFlash('success', 'Réservation supprimée !');
        }
        return $this->redirectToRoute('partner_dashboard');
    }
    #[Route('/partner/client/{clientId}/view', name: 'partner_view_client')]
public function viewClient(int $clientId, EntityManagerInterface $em): Response
{
    $client = $em->getRepository(Client::class)->find($clientId);
    return $this->render('partner/view_client.html.twig', [
        'client' => $client,
    ]);
}
#[Route('/partner/client/{clientId}/contact', name: 'partner_contact_client')]
public function contactClient(
    int $clientId,
    Request $request,
    EntityManagerInterface $em,
    MessageRepository $messageRepository,
    ClientRepository $clientRepository
): Response {
    $partner = $this->getUser();
    $client = $clientRepository->find($clientId);

    // Envoi du message
    if ($request->isMethod('POST')) {
        $content = $request->request->get('message');
        if ($content) {
            $message = new Message();
            $message->setSender($partner);
            $message->setReceiver($client);
            $message->setContent($content);
            $message->setSentAt(new \DateTime());
            $em->persist($message);
            $em->flush();
            $this->addFlash('success', 'Message envoyé.');
        }
        return $this->redirectToRoute('partner_contact_client', ['clientId' => $clientId]);
    }

    // Récupère la discussion
    $messages = $messageRepository->createQueryBuilder('m')
        ->where('(m.sender = :partner AND m.receiver = :client) OR (m.sender = :client AND m.receiver = :partner)')
        ->setParameter('partner', $partner)
        ->setParameter('client', $client)
        ->orderBy('m.sentAt', 'ASC')
        ->getQuery()
        ->getResult();

    return $this->render('partner/contact_client.html.twig', [
        'client' => $client,
        'messages' => $messages,
    ]);
}


#[Route('/partner/discussions', name: 'partner_all_discussions')]
public function allDiscussions(MessageRepository $messageRepository): Response
{
    $partner = $this->getUser();

    // Récupère tous les messages où le partenaire est impliqué
    $messages = $messageRepository->createQueryBuilder('m')
        ->where('m.sender = :partner OR m.receiver = :partner')
        ->setParameter('partner', $partner)
        ->getQuery()
        ->getResult();

    // Récupère tous les clients distincts avec qui il a discuté
    $clients = [];
    foreach ($messages as $message) {
        if ($message->getSender() instanceof \App\Entity\Client && $message->getSender() !== $partner) {
            $clients[$message->getSender()->getId()] = $message->getSender();
        }
        if ($message->getReceiver() instanceof \App\Entity\Client && $message->getReceiver() !== $partner) {
            $clients[$message->getReceiver()->getId()] = $message->getReceiver();
        }
    }

    return $this->render('partner/all_discussions.html.twig', [
        'clients' => $clients,
    ]);
}
}
