<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 23.04.2019
 * Time: 18:04
 */

namespace App\Service\Statistics;

use App\Entity\Strategy;
use App\Repository\Service\StrategyStatisticsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StrategyStatisticsService extends AbstractStatisticsService
{
    protected $repository;

    public function __construct(EntityManagerInterface $entityManager, StrategyStatisticsRepository $repository, ContainerInterface $container)
    {
        parent::__construct($entityManager, $container);

        $this->repository = $repository;
    }

    public function getStatisticsInfo(Strategy $strategy)
    {
        return $this->repository->getStrategyGamesResults($strategy);
    }
}