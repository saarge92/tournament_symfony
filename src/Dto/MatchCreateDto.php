<?php

namespace App\Dto;

class MatchCreateDto
{
    public int $idDivision;
    public int $idTeamHome;
    public int $idTeamGuest;
    public int $idTournament;
    public int $idStage;
    public int $countGoalTeamHome;
    public int $countGoalTeamGuest;
}