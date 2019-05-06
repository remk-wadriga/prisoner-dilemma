<?php


namespace App\Service\Statistics;

use App\Entity\User;
use App\Repository\Service\TotalStatisticsRepository;
use App\Service\FormatterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TotalStatisticsService extends AbstractStatisticsService
{
    protected $repository;

    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container, TotalStatisticsRepository $repository, FormatterService $formatter)
    {
        parent::__construct($entityManager, $container, $formatter);

        $this->repository = $repository;
    }

    public function getStatisticsByDates(User $user)
    {
        // Get statistics results
        $results = $this->repository->getStatisticsByDates($user);

        // Format statistics values and return formatted results
        return array_map(function ($result) {
            return array_merge($result, [
                'bales' => $this->formatter->toFloat($result['bales']),
                'gamesCount' => $this->formatter->toInt($result['gamesCount']),
                'roundsCount' => $this->formatter->toInt($result['roundsCount']),
            ]);
        }, $results);
    }

    public function getStatisticsByStrategies(User $user)
    {
        // Get statistics results
        $results = $this->repository->getStatisticsByStrategies($user);

        // Format statistics values and return formatted results
        return array_map(function ($result) {
            return array_merge($result, [
                'bales' => $this->formatter->toFloat($result['bales']),
                'gamesCount' => $this->formatter->toInt($result['gamesCount']),
                'roundsCount' => $this->formatter->toInt($result['roundsCount']),
            ]);
        }, $results);
    }

    public function getStatisticsByRoundsCount(User $user)
    {
        // Get statistics results
        $results = $this->repository->getStatisticsByRoundsCount($user);

        // Format statistics values and return formatted results
        return array_map(function ($result) {
            return array_merge($result, [
                'bales' => $this->formatter->toFloat($result['bales']),
                'gamesCount' => $this->formatter->toInt($result['gamesCount']),
                'roundsCount' => $this->formatter->toInt($result['roundsCount']),
            ]);
        }, $results);
    }
}