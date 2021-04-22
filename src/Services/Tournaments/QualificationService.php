<?php

namespace App\Services\Tournaments;

use App\Entity\Tournament;
use App\Interfaces\Tournaments\QualificationServiceInterface;
use App\Repository\DivisionRepository;
use App\Repository\TournamentMatchRepository;
use App\Repository\TournamentRepository;
use App\Repository\TournamentResultRepository;


class QualificationService implements QualificationServiceInterface
{
    public TournamentResultRepository $tournamentResultRepository;
    public TournamentRepository $tournamentRepository;
    public TournamentMatchRepository $matchRepository;
    private DivisionRepository $divisionRepository;

    public function __construct(
        TournamentResultRepository $tournamentResultRepository,
        TournamentRepository $tournamentRepository,
        TournamentMatchRepository $matchRepository,
        DivisionRepository $divisionRepository
    ) {
        $this->tournamentResultRepository = $tournamentResultRepository;
        $this->tournamentRepository = $tournamentRepository;
        $this->matchRepository = $matchRepository;
        $this->divisionRepository = $divisionRepository;
    }

    /**
     * @return array Вернем массив с данными турнира
     * @throws \Exception|\Doctrine\DBAL\Driver\Exception
     */
    public function getQualificationTournamentResult(int $tournamentId): array
    {
        $tournament = $this->tournamentRepository->find($tournamentId);
        if (!$tournament) {
            return [];
        }

        $tournamentResults = $this->tournamentResultRepository->getTournamentResultByTournamentIdGroupedByDivision(
            $tournamentId
        );
        if (count($tournamentResults) <= 0) {
            return [];
        }
        return $this->generateResponseForTournament($tournamentResults, $tournament);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function generateResponseForTournament(iterable $tournamentResults, Tournament $tournament): array
    {
        $response['tournament_id'] = $tournament->getId();
        $response['tournament_name'] = $tournament->getName();

        foreach ($tournamentResults as $divisionIndex => $divisionResults) {
            $tableRow['division_id'] = $divisionIndex;
            $division = $this->divisionRepository->find($divisionIndex);
            $division ? $tableRow['division_name'] = $division->getName() : $tableRow['division_name'] = null;

            $this->formatResponseForEveryDivisions($divisionResults, $tournament, $tableRow);
            $response['tables'][] = $tableRow;
            $tableRow = [];
        }
        return $response;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function formatResponseForEveryDivisions(
        iterable $divisionResults,
        Tournament $tournament,
        array &$tableRow
    ) {
        foreach ($divisionResults as $divisionResult) {
            $matchRow = [];
            $matchResults = $this->matchRepository->getMatchesByTeamIdAndTournament(
                $divisionResult->id_team,
                $tournament->getId(),
                1
            );
            foreach ($matchResults as $matchResult) {
                if ($matchResult->getIdTeamHome() == $divisionResult->id_team) {
                    $teamGuest = $divisionResults->where('id', $matchResult->getIdTeamGuest())->first();

                    $matchRow[$divisionResult->name][$teamGuest->name] = $matchResult->getCountGoalTeamHome() . ":"
                        . $matchResult->getCountGoalTeamGuest();
                } else {
                    if ($matchResult->getIdTeamGuest() == $divisionResult->id_team) {
                        $teamGuest = $divisionResults->where('id', $matchResult->getIdTeamHome())->first();

                        $matchRow[$divisionResult->name][$teamGuest->name] = $matchResult->getCountGoalTeamGuest() . ":"
                            . $matchResult->getCountGoalTeamHome();
                    }
                }
                $matchRow['score'] = $divisionResult->points;
            }
            $tableRow['results'][] = $matchRow;
        }
    }
}