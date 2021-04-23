<?php

namespace App\Interfaces\Tournaments;

use App\Dto\Responses\QualificationGenerationResponse;

interface QualificationServiceInterface
{
    public function getQualificationTournamentResult(int $tournamentId): ?QualificationGenerationResponse;
}