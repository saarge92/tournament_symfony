<?php

namespace App\Controller;

use App\Interfaces\Tournaments\QualificationGeneratorServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/qualification")
 */
class QualificationGeneratorController extends AbstractController
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
