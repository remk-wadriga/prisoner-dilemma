<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 12.09.2018
 * Time: 17:38
 */

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Exception\HttpException;

class AfterActionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'handleException',
        ];
    }

    public function handleException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!($exception instanceof HttpException)) {
            return;
        }
        $data = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'data' => [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ],
        ];
        $response = new JsonResponse(['error' => $data], $exception->getStatusCode());
        if (!empty($exception->getHeaders())) {
            $response->headers->add($exception->getHeaders());
        }
        $event->setResponse($response);
    }
}