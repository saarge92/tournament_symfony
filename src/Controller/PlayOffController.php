<?php

namespace App\Controller;

use App\Interfaces\Playoffs\PlayOffServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/playoff")
 */
class PlayOffController extends AbstractController
{
    protected PlayOffServiceInterface $playOffService;

    public function __construct(PlayOffServiceInterface $playOffService)
    {
        $this->playOffService = $playOffService;
    }

    /**
     * @Route("/tournament/{id}", methods={"GET"})
     */
    public function getPlayOffResult(int $id): JsonResponse
    {
        $playOffResults = $this->playOffService->getPlayOffResultsByTournamentId($id);
        return new JsonResponse($playOffResults);
    }
}
