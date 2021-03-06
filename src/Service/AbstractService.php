<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 20.09.2018
 * Time: 10:38
 */

namespace App\Service;

use Faker\Factory;
use Doctrine\ORM\EntityManagerInterface;
use App\Helpers\LoggerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractService
{
    use LoggerTrait;

    /** @var \Faker\Generator */
    protected $faker;
    protected $entityManager;
    protected $container;

    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
        $this->faker = Factory::create();
    }

    protected function getParam($name)
    {
        return $this->container->hasParameter($name) ? $this->container->getParameter($name) : null;
    }

    protected function getFrontendDateTimeFormat()
    {
        $format = $this->getParam('frontend_date_time_format');
        if ($format === null) {
            $format = 'Y-m-d H:i';
        }
        return $format;
    }
}