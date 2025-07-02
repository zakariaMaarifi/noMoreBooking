<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Hotel;
use App\Entity\Message;
use App\Entity\Partner;
use App\Entity\Purchase;
use App\Entity\Reservation;
use App\Entity\Review;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // Création d'un admin
        $admin = new Admin();
        $admin->setEmail('admin@test.com');
        $admin->setPassword($this->hasher->hashPassword($admin, 'password'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setSuperAdmin(true);
        $manager->persist($admin);

        // Création d'un client
        $client = new Client();
        $client->setEmail('client@test.com');
        $client->setPassword($this->hasher->hashPassword($client, 'password'));
        $client->setRoles(['ROLE_CLIENT']);
        $client->setFirstName('Jean');
        $client->setLastName('Dupont');
        $manager->persist($client);

        // Création d'un partenaire
        $partner = new Partner();
        $partner->setEmail('partner@test.com');
        $partner->setPassword($this->hasher->hashPassword($partner, 'password'));
        $partner->setRoles(['ROLE_PARTNER']);
        $partner->setCompanyName('BookingPro');
        $manager->persist($partner);

        // Création d'une catégorie
        $category = new Category();
        $category->setName('Luxe');
        $manager->persist($category);

        // Création d'un hôtel

        $hotelsData = [
            ['Hotel Royal', 'Paris'],
            ['Sunset Resort', 'Nice'],
            ['Mont Blanc Palace', 'Chamonix'],
            ['Ocean View Inn', 'Biarritz'],
            ['Château Lumière', 'Strasbourg'],
            ['Azur Paradise', 'Cannes'],
            ['Alpine Retreat', 'Grenoble'],
            ['City Zen Hotel', 'Lyon'],
            ['Riviera Escape', 'Marseille'],
            ['Golden Leaf', 'Toulouse'],
            ['La Belle Vie', 'Bordeaux'],
            ['Nordik Lodge', 'Lille'],
            ['Domaine du Lac', 'Annecy'],
            ['Le Central', 'Nantes'],
            ['Zenith Hôtel', 'Rennes'],
        ];

        foreach ($hotelsData as $index => [$name, $city]) {
            $hotel = new Hotel();
            $hotel->setName($name);
            $hotel->setCity($city);
            $hotel->setPartner($partner);
            $hotel->setStatus('available');
            $manager->persist($hotel);

            $startDate = new \DateTime("+" . (3 + $index * 2) . " days");
            $endDate = (clone $startDate)->modify('+2 days');

            $reservation = new Reservation();
            $reservation->setStartDate($startDate);
            $reservation->setEndDate($endDate);
            $reservation->setPrice(100 + rand(20, 100));
            $reservation->setIsAvailable(true);
            $reservation->setHotel($hotel);
            $reservation->addCategory($category);
            // $reservation->setClient($client);
            $reservation->setPartner($partner);
            $manager->persist($reservation);
        }

        // Création d'une réservation
        // $reservation = new Reservation();
        // $reservation->setStartDate(new \DateTime('+5 days'));
        // $reservation->setEndDate(new \DateTime('+7 days'));
        // $reservation->setPrice(150.00);
        // $reservation->setIsAvailable(true);
        // $reservation->setHotel($hotel);
        // $reservation->addCategory($category);
        // $reservation->setClient($client);
        // $reservation->setPartner($partner);
        // $manager->persist($reservation);

        // Création d'un achat
        $purchase = new Purchase();
        $purchase->setClient($client);
        $purchase->setReservation($reservation);
        $purchase->setPurchasedAt(new \DateTime());
        $manager->persist($purchase);

        // Création d'un avis
        $review = new Review();
        $review->setHotel($hotel);
        $review->setClient($client);
        $review->setRating(5);
        $review->setComment('Excellent séjour !');
        $manager->persist($review);

        // Création d'un message
        $message = new Message();
        $message->setSender($client);
        $message->setReceiver($partner);
        $message->setContent('Bonjour, avez-vous une chambre disponible ?');
        $message->setSentAt(new \DateTime());
        $manager->persist($message);

        $manager->flush();
    }
}
