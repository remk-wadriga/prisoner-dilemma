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
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class AfterActionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'handleResponse',
            KernelEvents::EXCEPTION => 'handleException',
        ];
    }

    public function handleResponse(FilterResponseEvent $event)
    {
        $event->getResponse()->headers->add([
            'access-control-expose-headers' => 'X-Debug-Token,X-Debug-Token-Link,Symfony-Debug-Toolbar-Replace',
            'Symfony-Debug-Toolbar-Replace' => 1,
        ]);
    }

    public function handleException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!($exception instanceof HttpException) && !($exception instanceof SymfonyHttpException)) {
            return;
        }

        if (!($exception instanceof HttpException) && $exception instanceof SymfonyHttpException) {
            $exception = new HttpException($exception->getMessage(), 0, $exception);
        }

        $data = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'data' => [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                //'trace' => $exception->getTrace(),
            ],
        ];

        $response = new JsonResponse(['error' => $data], $exception->getStatusCode());
        if (!empty($exception->getHeaders())) {
            $response->headers->add($exception->getHeaders());
        }
        $response->headers->add([
            'access-control-expose-headers' => 'X-Debug-Token,X-Debug-Token-Link',
        ]);
        $event->setResponse($response);
    }
}