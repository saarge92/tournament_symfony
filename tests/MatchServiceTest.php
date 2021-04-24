<?php

namespace App\Tests;

use App\Dto\MatchCreateDto;
use App\Entity\Division;
use App\Entity\Stage;
use App\Entity\Team;
use App\Entity\Tournament;
use App\Entity\TournamentMatch;
use App\Repository\DivisionRepository;
use App\Repository\StageRepository;
use App\Repository\TeamRepository;
use App\Repository\TournamentMatchRepository;
use App\Repository\TournamentRepository;
use App\Services\Matches\MatchService;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class MatchServiceTest extends KernelTestCase
{
    private DivisionRepository $divisionRepository;
    private TournamentRepository $tournamentRepository;
    private TeamRepository $teamRepository;
    private StageRepository $stageRepository;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $entityManager = self::$container->get('doctrine')->getManager();
        $this->divisionRepository = $entityManager->getRepository(Division::class);
        $this->tournamentRepository = $entityManager->getRepository(Tournament::class);
        $this->teamRepository = $entityManager->getRepository(Team::class);
        $this->stageRepository = $entityManager->getRepository(Stage::class);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testAddMatch()
    {
        $matchService = $this->getMatchService();
        $matchRepositoryMock = $this->createMock(TournamentMatchRepository::class);
        $division = $this->divisionRepository->getRandomDivision();
        $tournament = $this->tournamentRepository->getRandomTournament();
        $teamHome = $this->teamRepository->getRandomTeam();
        $teamGuest = $this->teamRepository->findOneByIdNotEqual($teamHome->getId())[0];
        $stage = $this->stageRepository->getRandomStage();
        $countGoalsHome = rand(1, 10);
        $countGoalsGuest = rand(1, 10);

        $data = new MatchCreateDto(
            $division->getId(),
            $teamHome->getId(),
            $teamGuest['id'],
            $tournament->getId(),
            $stage->getId(),
            $countGoalsHome,
            $countGoalsGuest
        );

        $response = $matchService->addMatchInfo($data);

        $this->assertInstanceOf(TournamentMatch::class, $response);

        $this->assertNotNull($matchService);
    }

    private function getMatchService(): MatchService
    {
        $container = self::$kernel->getContainer();
        return $container->get('match.service');
    }
}
