<?php

namespace App\Tests\Unit;

use App\Entity\Tournament;
use App\Interfaces\Playoffs\PlayOffGeneratorServiceInterface;
use App\Repository\TournamentRepository;
use App\Repository\TournamentResultRepository;
use App\Services\Playoffs\PlayOffGeneratorService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlayOffGeneratorServiceTest extends KernelTestCase
{
    private TournamentRepository $tournamentRepository;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $entityManager = self::$container->get('doctrine')
            ->getManager();
        $this->tournamentRepository = $entityManager->getRepository(Tournament::class);
    }

    public function testGeneratePlayoffForTournament()
    {
        $playoffGeneratorService = $this->getPlayOffGeneratorService();
        $randomTournament = $this->tournamentRepository->getRandomTournament();

        $keysForResponseCheckArray = [
            'quarter_final',
            'semifinal',
            'final_tournament',
            'third_place_tournament'
        ];

        $response = $playoffGeneratorService->generatePlayOfForTournament(
            $randomTournament->getId()
        );

        $this->assertIsArray($response);
        if (!empty($response)) {
            foreach ($keysForResponseCheckArray as $keyCheck) {
                $this->assertArrayHasKey($keyCheck, $response);
            }
        }
    }

    private function getPlayOffGeneratorService(): PlayOffGeneratorService
    {
        $container = self::$kernel->getContainer();
        return $container->get(PlayOffGeneratorService::class);
    }
}
