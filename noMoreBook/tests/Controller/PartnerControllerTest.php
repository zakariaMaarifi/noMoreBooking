<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PartnerControllerTest extends WebTestCase
{
    public function testIndex(): void
{
    $client = static::createClient();
    $container = static::getContainer();
    $partnerRepository = $container->get(\App\Repository\PartnerRepository::class);
    $partner = $partnerRepository->findOneBy(['email' => 'partner@test.com']);
    $client->loginUser($partner);

    $client->request('GET', '/partner');
    self::assertResponseIsSuccessful();
}
}
