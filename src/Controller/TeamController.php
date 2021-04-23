<?php

namespace App\Controller;

use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/team")
 */
class TeamController extends AbstractController
{
    protected TeamRepository $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    /**
     * @Route("/division/{id}", methods={"GET"})
     */
    public function getTeamsByDivisionId(int $id): JsonResponse
    {
        $teams = $this->teamRepository->getTeamByDivisionId($id);
        return new JsonResponse($teams, JsonResponse::HTTP_OK);
    }
}
