<?php

namespace App\Dto;

class MatchCreateDto
{
    public ?int $idDivision;
    public int $idTeamHome;
    public int $idTeamGuest;
    public int $idTournament;
    public int $idStage;
    public int $countGoalTeamHome;
    public int $countGoalTeamGuest;

    public function __construct(
        ?int $idDivision,
        int $idTeamHome,
        int $idTeamGuest,
        int $idTournament,
        int $idStage,
        int $countGoalTeamHome,
        int $countGoalTeamGuest
    ) {
        $this->idDivision = $idDivision;
        $this->idTeamHome = $idTeamHome;
        $this->idTeamGuest = $idTeamGuest;
        $this->idTournament = $idTournament;
        $this->idStage = $idStage;
        $this->countGoalTeamHome = $countGoalTeamHome;
        $this->countGoalTeamGuest = $countGoalTeamGuest;
    }
}