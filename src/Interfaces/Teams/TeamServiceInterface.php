<?php

namespace App\Interfaces\Teams;

interface TeamServiceInterface
{
    public function getTeamByDivisionId(int $divisionId): array;
}