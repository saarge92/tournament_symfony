<?php

namespace App\Services\Tournaments;

use App\Dto\Responses\QualificationGenerationResponse;
use App\Dto\Responses\TableQualificationElement;
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
     * @param int $tournamentId
     * @return QualificationGenerationResponse
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getQualificationTournamentResult(int $tournamentId): ?QualificationGenerationResponse
    {
        $tournament = $this->tournamentRepository->find($tournamentId);
        if (!$tournament) {
            return null;
        }

        $tournamentResults = $this->tournamentResultRepository->getTournamentResultByTournamentId(
            $tournamentId
        );
        if (count($tournamentResults) <= 0) {
            return null;
        }
        return $this->generateResponseForTournament($tournamentResults, $tournament);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function generateResponseForTournament(
        array $tournamentResults,
        Tournament $tournament
    ): QualificationGenerationResponse {
        $response = new QualificationGenerationResponse();
        $response->tournamentId = $tournament->getId();
        $response->tournamentName = $tournament->getName();
        $uniqueDivisionIndexes = array_unique(
            array_map(
                function ($element) {
                    return $element['id_division'];
                },
                $tournamentResults
            )
        );
        foreach ($uniqueDivisionIndexes as $divisionIndex) {
            $tableRow = new TableQualificationElement();
            $tableRow->divisionId = $divisionIndex;
            $division = $this->divisionRepository->find($divisionIndex);
            $division ? $tableRow->divisionName = $division->getName() : $tableRow->divisionName = "";
            $divisionResults = array_filter(
                $tournamentResults,
                function ($element) use ($divisionIndex) {
                    return $element['id_division'] == $divisionIndex;
                }
            );
            $this->formatResponseForEveryDivisions($divisionResults, $tournament, $tableRow);
            $response->tables[] = $tableRow;
            $tableRow = [];
        }

        return $response;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function formatResponseForEveryDivisions(
        array $divisionResults,
        Tournament $tournament,
        TableQualificationElement &$tableRow
    ): void {
        foreach ($divisionResults as $divisionResult) {
            $matchRow = [];
            $matchResults = $this->matchRepository->getMatchesByTeamIdAndTournament(
                $divisionResult['id_team'],
                $tournament->getId(),
                1
            );
            foreach ($matchResults as $matchResult) {
                if ($matchResult['idTeamHome'] == $divisionResult['id_team']) {
                    $teamGuest = array_values(
                        array_filter(
                            $divisionResults,
                            function ($element) use ($matchResult) {
                                return $element['id'] == $matchResult['idTeamGuest'];
                            }
                        )
                    )[0];
                    $matchRow[$divisionResult['name']][$teamGuest['name']] = $matchResult['countGoalTeamHome'] . ":"
                        . $matchResult['countGoalTeamGuest'];
                } else {
                    if ($matchResult['idTeamGuest'] == $divisionResult['id_team']) {
                        $teamGuest = array_values(
                            array_filter(
                                $divisionResults,
                                function ($element) use ($matchResult) {
                                    return $element['id'] == $matchResult['idTeamHome'];
                                }
                            )
                        )[0];
                        $matchRow[$divisionResult['name']][$teamGuest['name']] = $matchResult['countGoalTeamGuest'] . ":"
                            . $matchResult['countGoalTeamHome'];
                    }
                }
                $matchRow['score'] = $divisionResult['points'];
            }
            $tableRow->results[] = $matchRow;
        }
    }
}