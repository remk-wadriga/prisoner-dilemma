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
        $result = $this->repository->getStatisticsByStrategies($game);
        dd($result);
    }
}