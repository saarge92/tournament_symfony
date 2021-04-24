<?php

namespace App\Services\Playoffs;

use App\Entity\Tournament;
use App\Interfaces\Playoffs\PlayOffServiceInterface;
use App\Repository\ResultFinalRepository;
use App\Repository\TournamentMatchRepository;
use App\Repository\TournamentRepository;

class PlayOffService implements PlayOffServiceInterface
{
    protected TournamentRepository $tournamentRepository;
    protected TournamentMatchRepository $matchRepository;
    protected ResultFinalRepository $finaleRepository;

    public function __construct(
        TournamentRepository $tournamentRepository,
        TournamentMatchRepository $matchRepository,
        ResultFinalRepository $resultFinaleRepository
    ) {
        $this->tournamentRepository = $tournamentRepository;
        $this->matchRepository = $matchRepository;
        $this->finaleRepository = $resultFinaleRepository;
    }

    /**
     * @throws \Exception
     */
    public function getPlayOffResultsByTournamentId(int $tournamentId): array
    {
        $tournament = $this->tournamentRepository->find($tournamentId);
        if (!$tournament) {
            throw new \Exception("Такой турнир не найден", 409);
        }
        return $this->generateFullReviewForTournamentPlayOff($tournament);
    }

    private function generateFullReviewForTournamentPlayOff(Tournament $tournament): array
    {
        $response['id_tournament'] = $tournament->getId();
        $response['tournament_name'] = $tournament->getName();

        $idStageQuarterFinal = 2;
        $idStageSemifinal = 3;
        $idStageThirdPlace = 4;
        $idStageFinale = 5;

        $allFinalMatches = $this->matchRepository->getMatchesByTournamentForStages($tournament->getId(), [2, 3, 4, 5]);
        usort(
            $allFinalMatches,
            function ($elementLeft, $elementRight) {
                return $elementLeft['result_match']['stageId'] > $elementRight['result_match']['stageId'];
            }
        );
        $uniqueStages = array_unique(
            array_map(
                function ($element) {
                    return $element['result_match']['stageId'];
                },
                $allFinalMatches
            )
        );
        foreach ($uniqueStages as $stageIndex) {
            $finalResults = array_filter(
                $allFinalMatches,
                function ($element) use ($stageIndex) {
                    return $element['result_match']['stageId'] == $stageIndex;
                }
            );
            switch ($stageIndex) {
                case $idStageQuarterFinal:
                {
                    $this->initQuarterResponseForTournamentResponse($finalResults, $response);
                    break;
                }
                case $idStageSemifinal:
                {
                    $this->initSemifinal($finalResults, $response);
                    break;
                }
                case $idStageThirdPlace :
                {
                    $this->initThirdPlaceAndFinalMatches($finalResults, $response, 'third_place_tournament');
                    break;
                }
                case $idStageFinale:
                {
                    $this->initThirdPlaceAndFinalMatches($finalResults, $response, 'final_tournament');
                    break;
                }
            }
        }
        $response['final_results'] = $this->finaleRepository->getFinaleResultByTournamentId($tournament->getId());
        return $response;
    }

    private function initSemifinal(iterable $finalResults, array &$response): void
    {
        $response['semifinal'] = [];
        $resultMatchesRow = [];
        foreach ($finalResults as $finalResult) {
            $resultMatch = $finalResult['result_match'];
            $resultMatchesRow['result_matches'][] = $this->initOneMatchRow($resultMatch, $finalResult);
            if ($resultMatch['countGoalTeamHome'] > $resultMatch['countGoalTeamGuest']) {
                $this->initTeamWinnersHomeFromRow($resultMatchesRow, $resultMatch, $finalResult);
                $resultMatchesRow['third_place_teams'][] = [
                    'id' => $resultMatch['idTeamGuest'],
                    'name' => $finalResult['team_guest_name'],
                    'id_division' => $finalResult['team_guest_division']
                ];
            } else {
                if ($resultMatch['countGoalTeamHome'] < $resultMatch['countGoalTeamGuest']) {
                    $resultMatchesRow['team_winners'][] = [
                        'id' => $resultMatch['idTeamGuest'],
                        'name' => $finalResult['team_guest_name'],
                        'id_division' => $finalResult['team_guest_division']
                    ];
                    $resultMatchesRow['third_place_teams'][] = [
                        'id' => $resultMatch['idTeamHome'],
                        'name' => $finalResult['team_home_name'],
                        'id_division' => $finalResult['team_home_division']
                    ];
                }
            }
        }
        $response['semifinal'] = $resultMatchesRow;
    }

    private function initThirdPlaceAndFinalMatches(
        iterable $finalResults,
        array &$response,
        string $keyNameTournament
    ): void {
        foreach ($finalResults as $finalResult) {
            $resultMatch = $finalResult['result_match'];
            if ($resultMatch['countGoalTeamHome'] > $resultMatch['countGoalTeamGuest']) {
                $response[$keyNameTournament] = [
                    'winner' => [
                        'id' => $resultMatch['idTeamHome'],
                        'name' => $finalResult['team_home_name'],
                        'id_division' => $finalResult['team_home_division']
                    ],
                    'looser' => [
                        'id' => $resultMatch['idTeamGuest'],
                        'name' => $finalResult['team_guest_name'],
                        'id_division' => $finalResult['team_guest_division']
                    ],
                    'score' => $resultMatch['countGoalTeamHome'] . ":" . $resultMatch['countGoalTeamGuest']
                ];
            } else {
                if ($resultMatch['countGoalTeamHome'] < $resultMatch['countGoalTeamGuest']) {
                    $response[$keyNameTournament][] = [
                        'winner' => [
                            'id' => $resultMatch['idTeamGuest'],
                            'name' => $finalResult['team_guest_name'],
                            'id_division' => $finalResult['team_guest_division']
                        ],
                        'looser' => [
                            'id' => $resultMatch['idTeamGuest'],
                            'name' => $finalResult['team_home_name'],
                            'id_division' => $finalResult['team_home_division']
                        ],
                        'score' => $resultMatch['countGoalTeamGuest'] . ":" . $resultMatch['countGoalTeamHome']
                    ];
                }
            }
        }
    }

    private function initOneMatchRow(array $resultMatch, array $finalResult): array
    {
        return [
            'team_home' => [
                'id' => $resultMatch['idTeamHome'],
                'name' => $finalResult['team_home_name'],
                'id_division' => $finalResult['team_home_division']
            ],
            'team_guest' => [
                'id' => $resultMatch['idTeamGuest'],
                'name' => $finalResult['team_guest_name'],
                'id_division' => $finalResult['team_guest_division']
            ],
            'score' => $resultMatch['countGoalTeamHome'] . ":" . $resultMatch['countGoalTeamGuest']
        ];
    }

    private function initTeamWinnersHomeFromRow(array &$resultMatchesRow, array $resultMatch, array $finalResult)
    {
        $resultMatchesRow['team_winners'][] = [
            'id' => $resultMatch['idTeamHome'],
            'name' => $finalResult['team_home_name'],
            'id_division' => $finalResult['team_home_division']
        ];
    }

    private function initQuarterResponseForTournamentResponse(iterable $finalResults, array &$response)
    {
        $response['quarter_final'] = [];
        $resultMatchesRow = [];
        foreach ($finalResults as $finalResult) {
            $resultMatch = $finalResult['result_match'];
            $resultMatchesRow['result_matches'][] = $this->initOneMatchRow($resultMatch, $finalResult);
            if ($resultMatch['countGoalTeamHome'] > $resultMatch['countGoalTeamGuest']) {
                $this->initTeamWinnersHomeFromRow($resultMatchesRow, $resultMatch, $finalResult);
            } else {
                if ($resultMatch['countGoalTeamHome'] < $resultMatch['countGoalTeamGuest']) {
                    $resultMatchesRow['team_winners'][] = [
                        'id' => $resultMatch['idTeamGuest'],
                        'name' => $finalResult['team_guest_name'],
                        'id_division' => $finalResult['team_guest_division']
                    ];
                }
            }
        }
        $response['quarter_final'] = $resultMatchesRow;
    }
}
