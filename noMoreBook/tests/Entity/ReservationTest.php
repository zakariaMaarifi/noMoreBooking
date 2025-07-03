<?php
namespace App\Tests\Entity;

use App\Entity\Reservation;
use PHPUnit\Framework\TestCase;

class ReservationTest extends TestCase
{
    public function testIsAvailable()
    {
        $reservation = new Reservation();
        $reservation->setIsAvailable(true);
        $this->assertTrue($reservation->isAvailable());

        $reservation->setIsAvailable(false);
        $this->assertFalse($reservation->isAvailable());
    }
}
