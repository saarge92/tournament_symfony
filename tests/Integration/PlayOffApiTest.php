<?php

namespace App\Tests\Integration;

use App\Entity\Tournament;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlayOffApiTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testNotTournamentException(): void
    {
        $client = $this->createClient();
        $client->catchExceptions(true);
        $client->request('GET', '/api/playoff/tournament/-1');

        $response = $client->getResponse()->setTtl(2);
        $code = $response->getStatusCode();

        $this->assertEquals(409, $code);
    }

    public function testGenerateTournamentPlayoff(): void
    {
        $client = $this->createClient();
        $tournamentRepository = self::$container->get('doctrine')->getRepository(Tournament::class);
        $randomTournament = $tournamentRepository->getRandomTournament();
        $this->assertNotNull($tournamentRepository);

        $client->request('POST', "/api/playoff/tournament/" . $randomTournament->getId() . "/generate");
        $response = $client->getResponse();
        $body = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $responsesKeysToCheck = ['id_tournament', 'tournament_name', 'quarter_final', 'semifinal', 'final_tournament'];
        foreach ($responsesKeysToCheck as $responseKey) {
            $this->assertArrayHasKey($responseKey, $body);
        }
    }
}
