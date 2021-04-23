<?php

namespace App\Services\Playoffs;

use App\Dto\MatchCreateDto;
use App\Entity\ResultFinal;
use App\Entity\Team;
use App\Entity\Tournament;
use App\Entity\TournamentMatch;
use App\Interfaces\Matches\MatchServiceInterface;
use App\Interfaces\Playoffs\PlayOffGeneratorServiceInterface;
use App\Interfaces\Playoffs\PlayOffServiceInterface;
use App\Repository\ResultFinalRepository;
use App\Repository\StageRepository;
use App\Repository\TeamRepository;
use App\Repository\TournamentMatchRepository;
use App\Repository\TournamentRepository;
use App\Repository\TournamentResultRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PlayOffGeneratorService implements PlayOffGeneratorServiceInterface
{
    public TournamentResultRepository $tournamentResultRepository;
    public ResultFinalRepository $finaleRepository;
    public TournamentMatchRepository $matchRepository;
    public TournamentRepository $tournamentRepository;
    public PlayOffServiceInterface $playoffService;
    public MatchServiceInterface $matchService;
    public EntityManagerInterface $entityManager;
    private StageRepository $stageRepository;
    private TeamRepository $teamRepository;

    public function __construct(
        TournamentResultRepository $tournamentResultRepository,
        ResultFinalRepository $resultFinaleRepository,
        TournamentMatchRepository $matchRepository,
        TournamentRepository $tournamentRepository,
        PlayOffServiceInterface $playOffService,
        MatchServiceInterface $matchService,
        EntityManagerInterface $entityManager,
        StageRepository $stageRepository,
        TeamRepository $teamRepository
    ) {
        $this->tournamentResultRepository = $tournamentResultRepository;
        $this->finaleRepository = $resultFinaleRepository;
        $this->matchRepository = $matchRepository;
        $this->tournamentRepository = $tournamentRepository;
        $this->playoffService = $playOffService;
        $this->matchService = $matchService;
        $this->entityManager = $entityManager;
        $this->stageRepository = $stageRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @throws \Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function generatePlayOfForTournament(int $idTournament): array
    {
        $tournament = $this->tournamentRepository->find($idTournament);
        if (!$tournament) {
            throw new ConflictHttpException("Такой турнир не найен");
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
        /** @var Team $winner */
        $winner = $playoffResults['winner'];
        /** @var Team $looser */
        $looser = $playoffResults['looser'];
        $finalResponse['final_results'][] = [
            'id_team' => $winner->getId(),
            'name' => $winner->getName(),
            'id_division' => $winner->getIdDivision(),
            'place' => $placeWinner,
        ];
        $finalResponse['final_results'][] = [
            'id_team' => $looser->getId(),
            'name' => $looser->getName(),
            'id_division' => $looser->getIdDivision(),
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
        $teamHome = $this->teamRepository->find($teams[0]['id']);
        $teamGuest = $this->teamRepository->find($teams[1]['id']);
        if ($countGoalGuest == $countGoalHome) {
            $countGoalHome += 1;
        }
        $stage = $this->stageRepository->find($idStage);
        $this->matchRepository->save(
            new TournamentMatch(null, $teamHome, $teamGuest, $countGoalHome, $countGoalGuest, $stage, $tournament)
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
            $this->matchService->addMatchInfo(
                new MatchCreateDto(
                    null,
                    $teamHome['id'],
                    $teamGuest['id'],
                    $tournament->getId(),
                    3,
                    $countGoalTeamHome,
                    $countGoalTeamGuest
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
                    $teamHome['id'],
                    $teamGuest['id'],
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

    private function getShortInfoAboutTeamInfo(array $teamInfo): array
    {
        return [
            'id' => $teamInfo['id'],
            'name' => $teamInfo['name'],
            'id_division' => $teamInfo['id_division']
        ];
    }

    private function generateTopTeamResultByDivision(array $tournamentResults): array
    {
        $uniqueDivisionsId = array_unique(
            array_map(
                function ($element) {
                    return $element['id_division'];
                },
                $tournamentResults
            )
        );
        $response = [];
        foreach ($uniqueDivisionsId as $idDivision) {
            $groupedDivisionTournamentResult = array_filter(
                $tournamentResults,
                function ($element) use ($idDivision) {
                    return $element['id_division'] == $idDivision;
                }
            );
            usort(
                $groupedDivisionTournamentResult,
                function ($element1, $element2) {
                    return $element1['points'] < $element2['points'];
                }
            );
            $topFourTeams = array_slice($groupedDivisionTournamentResult, 0, 4);
            $response[$idDivision] = array_values($topFourTeams);
        }
        return $response;
    }
}