<?php

namespace App\Controller;

use App\Interfaces\Tournaments\QualificationGeneratorServiceInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/qualification")
 */
class QualificationGeneratorController extends AbstractFOSRestController
{
    private QualificationGeneratorServiceInterface $qualificationGeneratorService;

    public function __construct(QualificationGeneratorServiceInterface $qualificationGeneratorService)
    {
        $this->qualificationGeneratorService = $qualificationGeneratorService;
    }

    /**
     * @Route("/generate", name="qualification_generator", methods={"POST"})
     */
    public function generateQualification(): JsonResponse
    {
        $resultGeneration = $this->qualificationGeneratorService->generateQualificationGames();
        return new JsonResponse($resultGeneration, JsonResponse::HTTP_OK);
    }
}
