<?php

namespace App\Tests;

use App\Dto\MatchCreateDto;
use App\Entity\TournamentMatch;
use App\Repository\DivisionRepository;
use App\Repository\TournamentMatchRepository;
use App\Services\Matches\MatchService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class MatchServiceTest extends KernelTestCase
{
    private DivisionRepository $divisionRepository;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel(['environment' => 'test']);
        $entityManager = self::$container->get('doctrine')->getManager();
        $this->divisionRepository = $entityManager->getRepository(DivisionRepository::class);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testAddMatch()
    {
        $matchService = $this->getMatchService();
        $matchRepositoryMock = $this->createMock(TournamentMatchRepository::class);
        $division = $this->divisionRepository->getRandomDivision();
        $tournament = Tournament::orderByRaw("RAND()")->first();
        $team_home = Team::orderByRaw("RAND()")->first();
        $team_guest = Team::whereRaw("id != ?", [$team_home->id])->first();
        $stage = Stage::orderByRaw("RAND()")->first();
        $countGoalsHome = rand(1, 10);
        $countGoalsGuest = rand(1, 10);

        $data = [
            'id_division' => $division->id,
            'id_tournament' => $tournament->id,
            'id_team_home' => $team_home->id,
            'id_team_guest' => $team_guest->id,
            'id_stage' => $stage->id,
            'count_goal_team_home' => $countGoalsHome,
            'count_goal_team_guest' => $countGoalsGuest
        ];
        $data = new MatchCreateDto();

        $response = $matchService->addMatchInfo($data);
        $matchRepositoryMock->shouldReceive('createMatch')->with($data);
        $this->assertInstanceOf(TournamentMatch::class, $response);

        $this->assertNotNull($matchService);
    }

    private function getMatchService(): MatchService
    {
        $container = self::$kernel->getContainer();
        return $container->get('match.service');
    }
}
