<?php

namespace App\Interfaces\Tournaments;

interface QualificationServiceInterface
{
    public function getQualificationTournamentResult(int $tournamentId);
}