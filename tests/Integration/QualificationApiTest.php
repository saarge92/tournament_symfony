<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class QualificationApiTest extends WebTestCase
{
    public function testGenerateQualificationGames(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/qualification/generate');
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('tournamentId', $response);
        $this->assertArrayHasKey('tournamentName', $response);
        $this->assertArrayHasKey('tables', $response);
        $this->assertResponseIsSuccessful();
    }
}
