<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class QualificationApiTest extends WebTestCase
{
    public function testGenerateQualificationGames(): void
    {
        $client = static::createClient();
        $response = $client->request('POST', '/api/qualification/generate');

        $this->assertResponseIsSuccessful();

    }
}
