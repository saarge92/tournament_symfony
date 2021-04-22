<?php

namespace App\Services\Playoffs;

use App\Dto\MatchCreateDto;
use App\Entity\ResultFinal;
use App\Entity\Tournament;
use App\Entity\TournamentMatch;
use App\Interfaces\Matches\MatchServiceInterface;
use App\Interfaces\Playoffs\PlayOffGeneratorServiceInterface;
use App\Repository\ResultFinalRepository;
use App\Repository\StageRepository;
use App\Repository\TournamentMatchRepository;
use App\Repository\TournamentRepository;
use App\Repository\TournamentResultRepository;
use Doctrine\ORM\EntityManagerInterface;

class PlayOffGeneratorService implements PlayOffGeneratorServiceInterface
{
    public TournamentResultRepository $tournamentResultRepository;
    public ResultFinalRepository $finaleRepository;
    public TournamentMatchRepository $matchRepository;
    public TournamentRepository $tournamentRepository;
    public object $playoffService;
    public MatchServiceInterface $matchService;
    public EntityManagerInterface $entityManager;
    private StageRepository $stageRepository;

    public function __construct(
        TournamentResultRepository $tournamentResultRepository,
        ResultFinalRepository $resultFinaleRepository,
        TournamentMatchRepository $matchRepository,
        TournamentRepository $tournamentRepository,
        object $playOffService,
        MatchServiceInterface $matchService,
        EntityManagerInterface $entityManager
    ) {
        $this->tournamentResultRepository = $tournamentResultRepository;
        $this->finaleRepository = $resultFinaleRepository;
        $this->matchRepository = $matchRepository;
        $this->tournamentRepository = $tournamentRepository;
        $this->playoffService = $playOffService;
        $this->matchService = $matchService;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws \Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function generatePlayOfForTournament(int $idTournament): array
    {
        $tournament = $this->tournamentRepository->find($idTournament);
        if (!$tournament) {
            throw new \Exception("Такой турнир не найен");
        }

        $tournamentResults = $this->tournamentResultRepository->getTournamentResultByTournamentId(
            $idTournament
        );
        if (!$tournamentResults) {
            return [];
        }

        $resultFinale = $this->finaleRepository->getFinaleResultByTournamentId($idTournament);
        if (count($resultFinale) > 0) {
            return $this->playoffService->getPlayOffResultsByTournamentId($idTournament);
        }

        $groupedByDivisionTopTeamResult = $this->generateTopTeamResultByDivision($tournamentResults);
        $finalResponse['tournament_id'] = $tournament->getId();
        $finalResponse['tournament_name'] = $tournament->getName();

        $this->entityManager->beginTransaction();
        try {
            $quarterFinalResult = $this->generateQuarterFinale($groupedByDivisionTopTeamResult, $tournament);
            $finalResponse['quarter_final'] = $quarterFinalResult;

            $semifinalResults = $this->generateSemifinal($quarterFinalResult['team_winners'], $tournament);
            $finalResponse['semifinal'] = $semifinalResults;

            $thirdPlaceResult = $this->generateThirdPlaceAndFinal(
                $semifinalResults['third_place_teams'],
                $tournament,
                4,
                3,
                4
            );
            $finalResponse['third_place_tournament'] = $thirdPlaceResult;


            $finalResults = $this->generateThirdPlaceAndFinal(
                $semifinalResults['team_winners'],
                $tournament,
                5,
                1,
                2
            );
            $finalResponse['final_tournament'] = $finalResults;

            $this->fillFinaleResults($finalResponse, $finalResults, 1, 2);
            $this->fillFinaleResults($finalResponse, $thirdPlaceResult, 3, 4);
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            $this->entityManager->rollback();
            throw new \Exception($exception);
        }
        return $finalResponse;
    }

    private function fillFinaleResults(
        iterable &$finalResponse,
        iterable $playoffResults,
        int $placeWinner,
        int $placeLooser
    ): void {
        $finalResponse['final_results'][] = [
            'id_team' => $playoffResults['winner']['id'],
            'name' => $playoffResults['winner']['name'],
            'id_division' => $playoffResults['winner']['id_division'],
            'place' => $placeWinner,
        ];
        $finalResponse['final_results'][] = [
            'id_team' => $playoffResults['looser']['id'],
            'name' => $playoffResults['looser']['name'],
            'id_division' => $playoffResults['looser']['id_division'],
            'place' => $placeLooser
        ];
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    private function generateThirdPlaceAndFinal(
        array $teams,
        Tournament $tournament,
        int $idStage,
        int $placeWinner,
        int $placeLooser
    ): array {
        $response = [];
        $countGoalHome = rand(1, 10);
        $countGoalGuest = rand(1, 10);
        $teamHome = $teams[0];
        $teamGuest = $teams[1];
        if ($countGoalGuest == $countGoalHome) {
            $countGoalHome += 1;
        }
        $this->matchService->addMatchInfo(
            new MatchCreateDto(
                null,
                $teamHome->getId(),
                $teamGuest->getId(),
                $tournament->getId(),
                $idStage,
                $countGoalHome,
                $countGoalGuest
            )
        );
        if ($countGoalHome > $countGoalGuest) {
            $response['winner'] = $teamHome;
            $response['looser'] = $teamGuest;
            $response['score'] = $countGoalHome . ":" . $countGoalGuest;
            $this->finaleRepository->save(
                new ResultFinal($teamHome, $tournament, $placeWinner)
            );
            $this->finaleRepository->save(
                new ResultFinal($teamGuest, $tournament, $placeLooser)
            );
        } else {
            $response['winner'] = $teamGuest;
            $response['looser'] = $teamHome;
            $response['score'] = $countGoalGuest . ":" . $countGoalHome;
            $this->finaleRepository->save(
                new ResultFinal($teamGuest, $tournament, $placeWinner)
            );
            $this->finaleRepository->save(
                new ResultFinal($teamHome, $tournament, $placeLooser)
            );
        }
        return $response;
    }


    /**
     * @throws \Exception
     */
    private function generateSemifinal(array $teams, Tournament $tournament): array
    {
        if (count($teams) != 4) {
            throw new \Exception("Невозможно провести полуфинал, если команд больше или меньше 4");
        }

        $gamePlan = [0 => 3, 1 => 2];
        $response = [];
        foreach ($gamePlan as $firstIdTeam => $secondIdTeam) {
            $teamHome = $teams[$firstIdTeam];
            $teamGuest = $teams[$secondIdTeam];

            $countGoalTeamHome = rand(1, 10);
            $countGoalTeamGuest = rand(1, 10);
            if ($countGoalTeamHome == $countGoalTeamGuest) {
                $countGoalTeamHome += 1;
            }
            $stageSemifinal = $this->stageRepository->find(3);
            $this->matchRepository->save(
                new TournamentMatch(
                    null,
                    $teamHome,
                    $teamGuest,
                    $countGoalTeamHome,
                    $countGoalTeamGuest,
                    $stageSemifinal,
                    $tournament
                )
            );
            if ($countGoalTeamHome > $countGoalTeamGuest) {
                $response['team_winners'][] = $teamHome;
                $response['third_place_teams'][] = $teamGuest;
            } else {
                if ($countGoalTeamHome < $countGoalTeamGuest) {
                    $response['team_winners'][] = $teamGuest;
                    $response['third_place_teams'][] = $teamHome;
                }
            }
            $response['result_matches'][] = [
                'team_home' => $teamHome,
                'team_guest' => $teamGuest,
                'score' => $countGoalTeamHome . ":" . $countGoalTeamGuest
            ];
        }
        return $response;
    }

    /**
     * @throws \Exception
     */
    private function generateQuarterFinale(array $tournamentResults, Tournament $tournament): array
    {
        $gamePlans = [0 => 3, 1 => 2, 2 => 1, 3 => 0];
        $countDivisions = array_keys($tournamentResults);
        if (count($countDivisions) < 2) {
            throw new \Exception(
                "Невозможно провести четвертьфинал между дивизионами. Дивизионов должно быть всего два!"
            );
        }

        $teamsTopForQuarterFinaleFirstDivision = $tournamentResults[$countDivisions[0]];
        $teamsTopForQuarterFinaleSecondDivision = $tournamentResults[$countDivisions[1]];

        $semifinaleTeams = [];
        foreach ($gamePlans as $firstTeamPlace => $secondTeamPlace) {
            $teamHome = $teamsTopForQuarterFinaleFirstDivision[$firstTeamPlace];
            $teamGuest = $teamsTopForQuarterFinaleSecondDivision[$secondTeamPlace];

            $countGoalHome = rand(1, 10);
            $countGoalGuest = rand(1, 10);
            if ($countGoalHome == $countGoalGuest) {
                $countGoalHome += $countGoalHome + 1;
            }

            $this->matchService->addMatchInfo(
                new MatchCreateDto(
                    null,
                    $teamHome->getId(),
                    $teamGuest->getId(),
                    $tournament->getId(),
                    2,
                    $countGoalHome,
                    $countGoalGuest
                )
            );

            $semifinaleTeams['result_matches'][] = [
                'team_home' => $this->getShortInfoAboutTeamInfo($teamHome),
                'team_guest' => $this->getShortInfoAboutTeamInfo($teamGuest),
                'score' => $countGoalHome . ":" . $countGoalGuest
            ];

            if ($countGoalHome > $countGoalGuest) {
                $semifinaleTeams['team_winners'][] = $this->getShortInfoAboutTeamInfo($teamHome);
            } else {
                if ($countGoalHome < $countGoalGuest) {
                    $semifinaleTeams['team_winners'][] = $this->getShortInfoAboutTeamInfo($teamGuest);
                }
            }
        }
        return $semifinaleTeams;
    }

    /**
     *
     * @param \stdClass $teamInfo
     * @return array
     */
    private function getShortInfoAboutTeamInfo(\stdClass $teamInfo): array
    {
        return [
            'id' => $teamInfo->id,
            'name' => $teamInfo->name,
            'id_division' => $teamInfo->id_division
        ];
    }

    /**
     * @return array Вернем массив данных с топ 4 результатов для каждого дивизиона
     */
    private function generateTopTeamResultByDivision(array $tournamentResults): array
    {
        $groupedByDivisionTournamentResults = $tournamentResults->groupBy('id_division');
        $response = [];
        foreach ($groupedByDivisionTournamentResults as $divisionId => $divisionResults) {
            $orderedByPointsResult = $divisionResults->sortByDesc('points')->take(4);
            $response[$divisionId] = $orderedByPointsResult->values();
        }
        return $response;
    }
}