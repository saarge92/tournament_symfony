<?php

namespace App\Services\Tournaments;

use App\Dto\MatchCreateDto;
use App\Entity\Team;
use App\Entity\TournamentMatch;
use App\Interfaces\Matches\MatchServiceInterface;
use App\Interfaces\Tournaments\QualificationGeneratorServiceInterface;
use App\Interfaces\Tournaments\QualificationServiceInterface;
use App\Interfaces\Tournaments\TournamentResultServiceInterface;
use App\Repository\DivisionRepository;
use App\Repository\TournamentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class QualificationGeneratorService implements QualificationGeneratorServiceInterface
{
    protected TournamentRepository $tournamentRepository;
    protected EntityManagerInterface $entityManager;
    protected MatchServiceInterface $matchService;
    protected DivisionRepository $divisionRepository;
    private TournamentResultServiceInterface $tournamentResultService;
    private QualificationServiceInterface $qualificationTournamentService;

    public function __construct(
        TournamentRepository $tournamentRepository,
        EntityManagerInterface $entityManager,
        MatchServiceInterface $matchService,
        DivisionRepository $divisionRepository,
        QualificationServiceInterface $qualificationService,
        TournamentResultServiceInterface $tournamentResultService
    ) {
        $this->tournamentRepository = $tournamentRepository;
        $this->entityManager = $entityManager;
        $this->matchService = $matchService;
        $this->divisionRepository = $divisionRepository;
        $this->qualificationTournamentService = $qualificationService;
        $this->tournamentResultService = $tournamentResultService;
    }

    /**
     * @throws \Exception
     */
    public function generateQualificationGames(): array
    {
        $tournament = $this->tournamentRepository->getRandomTournament();
        if (!$tournament) {
            throw new \Exception("Турниры отсутсвуют в базе");
        }

        $tournamentResults = $this->qualificationTournamentService->getQualificationTournamentResult(
            $tournament->getId()
        );
        if (!empty($tournamentResults)) {
            return $tournamentResults;
        }

        $this->entityManager->beginTransaction();
        try {
            $response = [];
            $response['tournament_id'] = $tournament->getId();
            $response['tournament_name'] = $tournament->getName();

            $this->generateMatchesResponseForAllDivisions($tournament->getId(), $response);
        } catch (\Exception $ex) {
            $this->entityManager->rollback();
            throw new ConflictHttpException($ex);
        }
        $this->entityManager->commit();
        return $response;
    }

    private function generateMatchesResponseForAllDivisions(int $idTournament, array &$response): void
    {
        $divisions = $this->divisionRepository->findAll();
        foreach ($divisions as $division) {
            $tableRow['division_id'] = $division->getId();
            $tableRow['division_name'] = $division->getName();
            $teams = $division->getTeams()->toArray();

            /**@var Team $teamHome */
            foreach ($teams as $teamHome) {
                $teamRow[$teamHome->getName()] = [];

                /** @var Team $teamGuest */
                foreach ($teams as $teamGuest) {
                    if ($teamHome->getId() != $teamGuest->getId()) {
                        $matchHappened = $this->matchService->getMatchInfoOnTournamentStage(
                            $teamHome->getId(),
                            $teamGuest->getId(),
                            $idTournament,
                            1
                        );
                        $matchHappenedGuestSide = $this->matchService->getMatchInfoOnTournamentStage(
                            $teamGuest->getId(),
                            $teamHome->getId(),
                            $idTournament,
                            1
                        );
                        if (!$matchHappened && !$matchHappenedGuestSide) {
                            $match = $this->matchService->addMatchInfo(
                                new MatchCreateDto(
                                    $division->getId(), $teamHome->getId(), $teamGuest->getId(), $idTournament, 1,
                                    rand(1, 10), rand(1, 10)
                                )
                            );

                            $this->updateTeamResult($match, $teamGuest, $teamHome, $idTournament);

                            $teamRow[$teamHome->getName()][$teamGuest->getName()] = $match->getCountGoalTeamHome(
                                ) . ":" .
                                $match->getCountGoalTeamGuest();
                        } else {
                            if ($matchHappened) {
                                $teamRow[$teamHome->getName()][$teamGuest->getName(
                                )] = $matchHappened->getCountGoalTeamHome()
                                    . ":" . $matchHappened->getCountGoalTeamGuest();
                            } else {
                                if ($matchHappenedGuestSide) {
                                    $teamRow[$teamHome->getName()][$teamGuest->getName(
                                    )] = $matchHappenedGuestSide->getCountGoalTeamHome()
                                        . ":" . $matchHappenedGuestSide->getCountGoalTeamGuest();
                                }
                            }
                        }
                    }
                }
                $teamResult = $this->tournamentResultService->getTeamResultByTeamAndTournament(
                    $teamHome->getId(),
                    $idTournament
                );
                if ($teamResult) {
                    $teamRow['score'] = $teamResult->getPoints();
                } else {
                    $teamRow['score'] = 0;
                    $this->tournamentResultService->createTeamResult($teamHome->getId(), $idTournament, 0);
                }
                $tableRow['results'][] = $teamRow;
                $teamRow = [];
            }
            $response['tables'][] = $tableRow;
            $tableRow = [];
        }
    }

    private function updateTeamResult(TournamentMatch $match, Team $teamGuest, Team $teamHome, int $tournamentId): void
    {
        if ($match->getCountGoalTeamHome() == $match->getCountGoalTeamGuest()) {
            $this->tournamentResultService->updateTeamResult($teamGuest->getId(), $tournamentId, 1);
            $this->tournamentResultService->updateTeamResult($teamHome->getId(), $tournamentId, 1);
        } else {
            if ($match->getCountGoalTeamHome() > $match->getCountGoalTeamGuest()) {
                $this->tournamentResultService->updateTeamResult($teamHome->getId(), $tournamentId, 3);
            } else {
                if ($match->getCountGoalTeamHome() < $match->getCountGoalTeamGuest()) {
                    $this->tournamentResultService->updateTeamResult($teamGuest->getId(), $tournamentId, 3);
                }
            }
        }
    }
}