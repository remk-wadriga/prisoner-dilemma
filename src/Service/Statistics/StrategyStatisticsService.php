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
use App\Service\FormatterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StrategyStatisticsService extends AbstractStatisticsService
{
    protected $repository;

    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container, StrategyStatisticsRepository $repository, FormatterService $formatter)
    {
        parent::__construct($entityManager, $container, $formatter);

        $this->repository = $repository;
    }

    public function getStatisticsByRoundsCount(Strategy $strategy)
    {
        // Get statistics results
        $results = $this->repository->getStatisticsByRoundsCount($strategy);

        // Format statistics values and return formatted results
        return array_map(function ($result) {
            return array_merge($result, [
                'bales' => $this->formatter->toFloat($result['bales']),
                'gamesCount' => $this->formatter->toInt($result['gamesCount']),
            ]);
        }, $results);
    }
}