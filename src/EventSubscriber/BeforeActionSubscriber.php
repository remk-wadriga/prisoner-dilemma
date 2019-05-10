<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 05.09.2018
 * Time: 16:29
 */

namespace App\EventSubscriber;

use function json_last_error;
use function json_last_error_msg;
use App\Controller\ControllerAbstract;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class BeforeActionSubscriber implements EventSubscriberInterface
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'convertJsonStringToArray',
        ];
    }

    public function convertJsonStringToArray(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $controller = $event->getController();

        if (is_array($controller) && isset($controller[0])) {
            $controller = $controller[0];
        }
        if ($controller instanceof ControllerAbstract && !empty($controller->getRequestFilters())) {
            foreach ($controller->getRequestFilters() as $param) {
                $value = $request->get($param);
                if ($value === null) {
                    continue;
                }
                $doctrineFilterName = $param . '_filter';
                if ($this->entityManager->getFilters()->has($doctrineFilterName)) {
                    $filter = $this->entityManager->getFilters()->enable($doctrineFilterName);
                    $filter->setParameter($param, $value);
                }
            }
        }

        if ($request->getContentType() !== 'json' || empty($request->getContent())) {
            return;
        }
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestHttpException('invalid json body: ' . json_last_error_msg());
        }
        $request->request->replace(is_array($data) ? $data : []);
    }

}