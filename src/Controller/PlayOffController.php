<?php

namespace App\Controller;

use App\Interfaces\Playoffs\PlayOffServiceInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/playoff")
 */
class PlayOffController extends AbstractFOSRestController
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
