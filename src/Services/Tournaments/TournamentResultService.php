<?php

namespace App\Services\Tournaments;

use App\Entity\TournamentResult;
use App\Exceptions\ServerException;
use App\Interfaces\Tournaments\TournamentResultServiceInterface;
use App\Repository\TeamRepository;
use App\Repository\TournamentRepository;
use App\Repository\TournamentResultRepository;

class TournamentResultService implements TournamentResultServiceInterface
{
    protected TournamentRepository $tournamentRepository;
    protected TournamentResultRepository $tournamentResultRepository;
    protected TeamRepository $teamRepository;

    public function __construct(
        TournamentRepository $tournamentRepository,
        TournamentResultRepository $tournamentResultRepository,
        TeamRepository $teamRepository
    ) {
        $this->tournamentRepository = $tournamentRepository;
        $this->tournamentResultRepository = $tournamentResultRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @throws \Exception
     */
    public function createTeamResult(int $idTeam, int $idTournament, int $points): TournamentResult
    {
        $tournament = $this->tournamentRepository->find($idTournament);
        if (!$tournament) {
            throw new ServerException("Такой турнир отсутсвует в базе");
        }
        $existedTeamTournament = $this->tournamentResultRepository->findOneBy(
            ['idTeam' => $idTeam, 'idTournament' => $idTournament]
        );
        if ($existedTeamTournament) {
            throw new ServerException("Данные турнира для этой команды уже существует");
        }
        $team = $this->teamRepository->find($idTeam);
        if (!$team) {
            throw new ServerException("Такая команда не найдена");
        }
        if ($points < 0) {
            throw new ServerException("Очки не могут быть отрицательными");
        }
        $tournamentResult = new TournamentResult($tournament, $team, $points);
        $this->tournamentResultRepository->save($tournamentResult);
        return $tournamentResult;
    }

    /**
     * @throws \Exception
     */
    public function updateTeamResult(int $idTeam, int $idTournament, int $newPoint): TournamentResult
    {
        if ($newPoint < 0) {
            throw new ServerException("Присваиваемые очки не могут быть отрицательными");
        }

        $tournament = $this->tournamentRepository->find($idTournament);
        if (!$tournament) {
            throw new ServerException("Такой турнир отсутсвует в базе");
        }
        $team = $this->teamRepository->find($idTeam);
        if (!$team) {
            throw new ServerException("Такая команда не найден");
        }

        $existedRecordTeamResult = $this->tournamentResultRepository->findOneBy(
            ['idTeam' => $idTeam, 'idTournament' => $idTournament]
        );
        if (!$existedRecordTeamResult) {
            $tournamentResult = new TournamentResult($tournament, $team, $newPoint);
            $this->tournamentResultRepository->save(
                $tournamentResult
            );
            return $tournamentResult;
        }

        $existedRecordTeamResult->setPoints($existedRecordTeamResult->getPoints() + $newPoint);
        $this->tournamentResultRepository->save($existedRecordTeamResult);
        return $existedRecordTeamResult;
    }

    public function getTeamResultByTeamAndTournament(int $idTeam, int $idTournament): ?TournamentResult
    {
        return $this->tournamentResultRepository->findOneBy(['idTeam' => $idTeam, 'idTournament' => $idTournament]);
    }
}