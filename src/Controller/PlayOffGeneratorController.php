<?php

namespace App\Controller;

use App\Interfaces\Playoffs\PlayOffGeneratorServiceInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/playoff")
 */
class PlayOffGeneratorController extends AbstractFOSRestController
{
    protected PlayOffGeneratorServiceInterface $playOffGeneratorService;

    public function __construct(PlayOffGeneratorServiceInterface $playOffGeneratorService)
    {
        $this->playOffGeneratorService = $playOffGeneratorService;
    }

    /**
     * @Route("/tournament/{id}/generate", name="playoff_generate")
     */
    public function generateTournamentPlayOff(int $id): JsonResponse
    {
        $playoffsResult = $this->playOffGeneratorService->generatePlayOfForTournament($id);
        return new JsonResponse($playoffsResult);
    }
}
