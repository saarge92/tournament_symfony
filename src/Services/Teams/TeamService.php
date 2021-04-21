<?php

namespace App\Services\Teams;

use App\Entity\Team;
use App\Interfaces\Teams\TeamServiceInterface;
use App\Repository\TeamRepository;

class TeamService implements TeamServiceInterface
{
    protected TeamRepository $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    /**
     * @return Team[]
     */
    public function getTeamByDivisionId(int $divisionId): array
    {
        return $this->teamRepository->findBy(['id_division' => $divisionId], ['id' => 'desc']);
    }
}