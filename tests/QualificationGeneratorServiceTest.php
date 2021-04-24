<?php

namespace App\Tests;

use App\Dto\Responses\QualificationGenerationResponse;
use App\Services\Tournaments\QualificationGeneratorService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class QualificationGeneratorServiceTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testGenerateQualificationGames(): void
    {
        $qualificationGeneratorService = $this->getQualificationGeneratorService();

        $response = $qualificationGeneratorService->generateQualificationGames();

        $this->assertIsArray($response->tables);
        $this->assertInstanceOf(QualificationGenerationResponse::class, $response);
    }

    private function getQualificationGeneratorService(): QualificationGeneratorService
    {
        $container = self::$kernel->getContainer();
        return $container->get(QualificationGeneratorService::class);
    }
}
