<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\HotelRepository;

final class ClientControllerTest extends WebTestCase
{
   public function testIndex(): void
{
    $client = static::createClient();
    $container = static::getContainer();
    $userRepository = $container->get(\App\Repository\ClientRepository::class);
    $user = $userRepository->findOneBy([]);
    $client->loginUser($user);

    $client->request('GET', '/client');
    self::assertResponseRedirects('/client/dashboard');
    $client->followRedirect();
    self::assertResponseIsSuccessful();
}

    public function testOfferDetailPageIsAccessible(): void
    {
    $client = static::createClient();
    $container = static::getContainer();
    $userRepository = $container->get(\App\Repository\ClientRepository::class);
    $user = $userRepository->findOneBy(['email' => 'client@test.com']);
    $client->loginUser($user);

    $hotelRepository = $container->get(\App\Repository\HotelRepository::class);
    $hotel = $hotelRepository->findOneBy([]);
    $this->assertNotNull($hotel, 'Aucun hôtel trouvé en base.');

    $client->request('GET', '/client/offre/' . $hotel->getId());
    self::assertResponseIsSuccessful();
    self::assertSelectorExists('h1');
    }
}
