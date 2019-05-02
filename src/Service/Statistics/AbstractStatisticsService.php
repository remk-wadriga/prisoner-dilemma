<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 23.04.2019
 * Time: 18:05
 */

namespace App\Service\Statistics;

use App\Service\AbstractService;
use App\Service\FormatterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AbstractStatisticsService extends AbstractService
{
    protected $formatter;

    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container, FormatterService $formatter)
    {
        parent::__construct($entityManager, $container);

        $this->formatter = $formatter;
    }
}