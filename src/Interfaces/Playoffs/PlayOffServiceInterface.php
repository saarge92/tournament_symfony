<?php

namespace App\Interfaces\Playoffs;

interface PlayOffServiceInterface
{
    public function getPlayOffResultsByTournamentId(int $tournamentId);
}