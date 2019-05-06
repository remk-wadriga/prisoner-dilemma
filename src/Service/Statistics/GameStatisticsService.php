<?php


namespace App\Service\Statistics;

use App\Entity\Game;
use App\Repository\Service\GameStatisticsRepository;
use App\Service\FormatterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GameStatisticsService extends AbstractStatisticsService
{
    protected $repository;

    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container, GameStatisticsRepository $repository, FormatterService $formatter)
    {
        parent::__construct($entityManager, $container, $formatter);

        $this->repository = $repository;
    }

    public function getStatisticsByStrategies(Game $game)
    {
        // Get statistics results
        $results = $this->repository->getStatisticsByStrategies($game);

        // Format statistics values and return formatted results
        return array_map(function ($result) {
            return array_merge($result, [
                'bales' => $this->formatter->toInt($result['bales']),
            ]);
        }, $results);
    }
}