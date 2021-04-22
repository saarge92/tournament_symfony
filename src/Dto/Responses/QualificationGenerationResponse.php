<?php

namespace App\Dto\Responses;


class QualificationGenerationResponse
{
    public int $tournamentId;
    public string $tournamentName;
    /** @var TableQualificationElement[] */
    public array $tables;
}