<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlayOffApiTest extends WebTestCase
{
    public function testNotTournamentException(): void
    {
        $client = static::createClient();
        $client->catchExceptions(true);
        $client->request('GET', '/api/playoff/tournament/-1');

        $response = $client->getResponse()->setTtl(2);
        $code = $response->getStatusCode();

        $this->assertEquals(409, $code);
    }
}
