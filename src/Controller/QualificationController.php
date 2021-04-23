<?php

namespace App\Controller;

use App\Interfaces\Tournaments\QualificationServiceInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/qualification")
 */
class QualificationController extends AbstractFOSRestController
{
    private QualificationServiceInterface $qualificationService;

    public function __construct(QualificationServiceInterface $qualificationService)
    {
        $this->qualificationService = $qualificationService;
    }

    /**
     * @Route("/tournament/{id}", name="qualification_get", methods={"GET"})
     */
    public function getQualificationTournament(int $id): JsonResponse
    {
        $response = $this->qualificationService->getQualificationTournamentResult($id);
        return new JsonResponse($response, JsonResponse::HTTP_OK);
    }
}
