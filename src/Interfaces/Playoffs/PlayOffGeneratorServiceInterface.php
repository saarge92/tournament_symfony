<?php

namespace App\Interfaces\Playoffs;

interface PlayOffGeneratorServiceInterface
{
    public function generatePlayOfForTournament(int $idTournament): array;
}