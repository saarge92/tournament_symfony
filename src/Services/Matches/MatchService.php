<?php

namespace App\Services\Matches;

use App\Dto\MatchCreateDto;
use App\Entity\TournamentMatch;
use App\Interfaces\Matches\MatchServiceInterface;
use App\Repository\DivisionRepository;
use App\Repository\StageRepository;
use App\Repository\TeamRepository;
use App\Repository\TournamentMatchRepository;
use App\Repository\TournamentRepository;

class MatchService implements MatchServiceInterface
{
    private TournamentMatchRepository $tournamentMatchRepository;
    private DivisionRepository $divisionRepository;
    private TeamRepository $teamRepository;
    private StageRepository $stageRepository;
    private TournamentRepository $tournamentRepository;

    public function __construct(
        TournamentMatchRepository $tournamentMatchRepository,
        DivisionRepository $divisionRepository,
        TeamRepository $teamRepository,
        StageRepository $stageRepository,
        TournamentRepository $tournamentRepository
    ) {
        $this->tournamentMatchRepository = $tournamentMatchRepository;
        $this->divisionRepository = $divisionRepository;
        $this->teamRepository = $teamRepository;
        $this->stageRepository = $stageRepository;
        $this->tournamentRepository = $tournamentRepository;
    }

    /**
     * @throws \Exception
     */
    public function addMatchInfo(MatchCreateDto $matchCreateDto): TournamentMatch
    {
        $division = null;
        if ($matchCreateDto->idDivision) {
            $division = $this->divisionRepository->find($matchCreateDto->idDivision);
            if (!$division) {
                throw new \Exception("Дивизион с таким id_division не найден");
            }
        }

        $teamHome = $this->teamRepository->find($matchCreateDto->idTeamHome);
        if (!$teamHome) {
            throw new \Exception("Отсутсвует такая команда с id_team_home");
        }

        $teamGuest = $this->teamRepository->find($matchCreateDto->idTeamGuest);
        if (!$teamGuest) {
            throw new \Exception("Команда с таким id_team_guest не найдена");
        }

        if ($matchCreateDto->countGoalTeamHome < 0 || $matchCreateDto->countGoalTeamGuest < 0) {
            throw new \Exception("Счет не может быть отрицательным");
        }

        $stage = $this->stageRepository->find($matchCreateDto->idStage);
        if (!$stage) {
            throw new \Exception('Этап соревнований с таким id_stage не найден');
        }

        $tournament = $this->tournamentRepository->find($matchCreateDto->idTournament);
        if (!$tournament) {
            throw new \Exception('Турнир не найден с таким id_tournament');
        }

        $tournamentMatch = new TournamentMatch(
            $division,
            $teamHome,
            $teamGuest,
            $matchCreateDto->countGoalTeamHome,
            $matchCreateDto->countGoalTeamGuest,
            $stage,
            $tournament
        );
        $this->tournamentMatchRepository->save($tournamentMatch);
        return $tournamentMatch;
    }

    public function getMatchInfoOnTournamentStage(
        int $idHomeTeam,
        int $idTeamGuest,
        int $tournamentId,
        int $idStage
    ): ?TournamentMatch {
        return $this->tournamentMatchRepository->findOneBy(
            [
                'idTeamHome' => $idHomeTeam,
                'idTeamGuest' => $idTeamGuest,
                'tournamentId' => $tournamentId,
                'stageId' => $idStage
            ]
        );
    }

    /**
     * @return TournamentMatch[]
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getMatchesForTeam(int $teamId, int $tournament, int $idStage): array
    {
        return $this->tournamentMatchRepository->getMatchesByTeamIdAndTournament($teamId, $tournament, $idStage);
    }
}