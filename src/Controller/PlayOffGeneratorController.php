<?php

namespace App\Controller;

use App\Interfaces\Playoffs\PlayOffGeneratorServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/playoff")
 */
class PlayOffGeneratorController extends AbstractController
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
