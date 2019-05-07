<?php

namespace App\Tests\Unit\Statistics;

use App\Entity\Game;
use App\Entity\GameResult;
use App\Repository\Service\GameStatisticsRepository;
use App\Service\Statistics\GameStatisticsService;
use Faker\Factory;

class GameStatisticsTest extends AbstractStatisticsUnitTestCase
{
    protected $statisticsService;
    protected $repository;
    protected $randomGame;

    public function testStatisticsByStrategies()
    {
        $testKeysID = 'game_statistics_by_strategies';

        // 1. Find random game with games statistics
        $game = $this->getRandomGame();

        // 2. Get game statistics
        $statistics = $this->getStatisticsService()->getStatisticsByStrategies($game);

        // 3. Check statistics data (must be an array and all elements must have all necessary attributes with correct types)
        $this->checkStatisticsData($statistics, $testKeysID, [
            'strategy' => 'string',
            'bales' => 'integer',
        ]);

        // 4. Get statistics data from DB
        $statsQuery = $this->entityManager->createQueryBuilder()
            ->select('SUM(gr.result)')
            ->from(GameResult::class, 'gr')
            ->andWhere('gr.game = :game')
            ->setParameter('game', $game)
        ;
        $dbStatisticsBales = $statsQuery->getQuery()->getSingleScalarResult();
        $dbStatisticsBales = $this->getFormatterService()->toInt($dbStatisticsBales);

        // 5. Calculate statistics that was returned from function
        $bales = 0;
        foreach ($statistics as $stats) {
            $bales += $stats['bales'];
        }

        // 6. Check is statistics calculated by service equal to statistics from DB
        $this->assertEquals($bales, $dbStatisticsBales, sprintf('Test keys "%s" failed. Statistics for #%s game "bales" must have %s value but %s given',
            $testKeysID, $game->getId(), $bales, $dbStatisticsBales));
    }


    protected function getRandomGame(): Game
    {
        if ($this->randomGame !== null) {
            return $this->randomGame;
        }
        $gameRepository = $this->entityManager->getRepository(Game::class);
        $gameResultRepository = $this->entityManager->getRepository(GameResult::class);

        $gamesIDsQuery = $gameResultRepository->createQueryBuilder('gr')
            ->select('g.id')
            ->innerJoin('gr.game', 'g')
            ->setMaxResults(100)
        ;

        $ids = array_map(function ($result) { return intval($result['id']); }, $gamesIDsQuery->getQuery()->getScalarResult());
        $faker = Factory::create();
        $id = $faker->randomElement($ids);

        return $this->randomGame = $gameRepository->findOneBy(['id' => $id]);
    }

    protected function getStatisticsService(): GameStatisticsService
    {
        if ($this->statisticsService !== null) {
            return $this->statisticsService;
        }

        return $this->statisticsService = new GameStatisticsService($this->entityManager, self::$kernel->getContainer(), $this->getRepository(), $this->getFormatterService());
    }

    protected function getRepository(): GameStatisticsRepository
    {
        if ($this->repository !== null) {
            return $this->repository;
        }

        return $this->repository = new GameStatisticsRepository($this->entityManager, self::$kernel->getContainer());
    }
}