<?php

namespace App\EventListeners;

use App\Exceptions\ServerException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class JsonException
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $code = $exception instanceof ServerException ? $exception->getCode() :  500;
        $responseData = [
            'error' => [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ]
        ];
        $event->setResponse(new JsonResponse($responseData, $code));
    }
}