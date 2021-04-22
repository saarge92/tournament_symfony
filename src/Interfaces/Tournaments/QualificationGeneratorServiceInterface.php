<?php

namespace App\Interfaces\Tournaments;

use App\Dto\Responses\QualificationGenerationResponse;

interface QualificationGeneratorServiceInterface
{
    public function generateQualificationGames(): QualificationGenerationResponse;
}