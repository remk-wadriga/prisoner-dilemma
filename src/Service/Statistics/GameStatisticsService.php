<?php


namespace App\Service\Statistics;

use App\Entity\Game;
use App\Entity\GameResult;
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

    public function getStatisticsByDates(Game $game)
    {
        // Get statistics results
        $results = $this->repository->getStatisticsByDates($game);

        // Get game best and worse results
        $gameResultsRepository = $this->entityManager->getRepository(GameResult::class);
        $bestResult = $gameResultsRepository->findGameBestResult($game);
        $worseResult = $gameResultsRepository->findGameWorseResult($game);

        // Format statistics values and return formatted results
        return array_map(function ($result) use ($bestResult, $worseResult) {
            return array_merge($result, [
                'bales' => $this->formatter->toInt($result['bales']),
                'roundsCount' => $this->formatter->toInt($result['roundsCount']),
                'winner' => $bestResult,
                'loser' => $worseResult,
            ]);
        }, $results);
    }
}