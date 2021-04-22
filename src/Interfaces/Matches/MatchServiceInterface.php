<?php

namespace App\Interfaces\Matches;

use App\Dto\MatchCreateDto;
use App\Entity\TournamentMatch;

interface MatchServiceInterface
{
    public function addMatchInfo(MatchCreateDto $matchCreateDto): TournamentMatch;

    public function getMatchInfoOnTournamentStage(int $idHomeTeam, int $idTeamGuest, int $tournamentId, int $idStage);

    public function getMatchesForTeam(int $teamId, int $tournament, int $idStage);
}