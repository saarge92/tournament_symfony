<?php

namespace App\Interfaces\Tournaments;

use App\Entity\TournamentResult;

interface TournamentResultServiceInterface
{
    public function createTeamResult(int $idTeam, int $idTournament, int $points): TournamentResult;

    public function updateTeamResult(int $idTeam, int $idTournament, int $newPoint): TournamentResult;

    public function getTeamResultByTeamAndTournament(int $idTeam, int $idTournament): ?TournamentResult;
}