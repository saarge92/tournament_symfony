<?php

namespace App\Services\Tournaments;

use App\Interfaces\Tournaments\QualificationGeneratorServiceInterface;
use App\Repository\TournamentRepository;

class QualificationGeneratorService implements QualificationGeneratorServiceInterface
{
    protected TournamentRepository $tournamentRepository;

    public function __construct(TournamentRepository $tournamentRepository)
    {
        $this->tournamentRepository = $tournamentRepository;
    }

    /**
     * @throws \Exception
     */
    public function generateQualificationGames()
    {
        $tournament = $this->tournamentRepository->getRandomTournament();
        if (!$tournament) {
            throw new \Exception("Турниры отсутсвуют в базе");
        }

//        $tournamentResults = $this->qualificationTournamentService->getQualificationTournamentResult($tournament->id);
//        if (!empty($tournamentResults))
//            return $tournamentResults;

    }
}