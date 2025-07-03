<?php
namespace App\Controller\Api;

use App\Repository\HotelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class HotelApiController extends AbstractController
{
    #[Route('/api/hotels/available', name: 'api_hotels_available', methods: ['GET'])]
    public function availableHotels(HotelRepository $hotelRepository, SerializerInterface $serializer): JsonResponse
    {
        $hotels = $hotelRepository->findBy(['status' => 'available']);

        $json = $serializer->serialize($hotels, 'json', ['groups' => ['api']]);

        return new JsonResponse($json, 200, [], true);
    }
 #[Route('/weather/{city}', name: 'weather_page', methods: ['GET'])]
public function weatherPage(string $city, HttpClientInterface $httpClient): Response
{
    $coordinates = [
        'paris' => ['lat' => 48.8566, 'lon' => 2.3522],
        'lyon' => ['lat' => 45.7640, 'lon' => 4.8357],
        'marseille' => ['lat' => 43.2965, 'lon' => 5.3698],
        'lille' => ['lat' => 50.6292, 'lon' => 3.0573],
        'bordeaux' => ['lat' => 44.8378, 'lon' => -0.5792],
        'casablanca' => ['lat' => 33.589886, 'lon' => -7.603869]

    ];

    $cityKey = strtolower($city);
    if (!isset($coordinates[$cityKey])) {
        throw $this->createNotFoundException('Ville non supportée');
    }

    $lat = $coordinates[$cityKey]['lat'];
    $lon = $coordinates[$cityKey]['lon'];

    $response = $httpClient->request(
        'GET',
        "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&current_weather=true"
    );

    $data = $response->toArray();

    return $this->render('weather/show.html.twig', [
        'ville' => ucfirst($cityKey),
        'temperature' => $data['current_weather']['temperature'],
    ]);
}
#[Route('/test-mail')]
public function testMail(MailerInterface $mailer)
{
    $email = (new Email())
        ->from('test@example.com')
        ->to('test@fake.com')
        ->subject('Test Mailtrap')
        ->text('Ceci est un test');
    $mailer->send($email);
    return new Response('Mail envoyé');
}
}
