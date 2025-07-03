<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
         $client = static::createClient();
    $container = static::getContainer();
    $adminRepository = $container->get(\App\Repository\AdminRepository::class);
    $admin = $adminRepository->findOneBy(['email' => 'admin@test.com']);
    $client->loginUser($admin);

    $client->request('GET', '/admin');
    self::assertResponseIsSuccessful();
    }
}
