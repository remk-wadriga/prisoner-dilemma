<?php

namespace App\Tests\Unit\Statistics;

use App\Entity\GameResult;
use App\Entity\Strategy;
use App\Repository\Service\StrategyStatisticsRepository;
use App\Service\Statistics\StrategyStatisticsService;
use Faker\Factory;

class StrategyStatisticsTest extends AbstractStatisticsUnitTestCase
{
    protected $statisticsService;
    protected $repository;
    protected $randomStrategy;

    public function testStatisticsByDates()
    {
        $testKeysID = 'strategy_statistics_by_dates';
        $formatter = $this->getFormatterService();

        // 1. Find random strategy with games statistics
        $strategy = $this->getRandomStrategy();

        // 2. Get strategy statistics
        $statistics = $this->getStatisticsService()->getStatisticsByDates($strategy);

        // 3. Check statistics data (must be an array and all elements must have all necessary attributes with correct types)
        $this->checkStatisticsData($statistics, $testKeysID, [
            'bales' => 'float',
            'gamesCount' => 'integer',
            'gameDate' => 'string',
        ]);

        // 4. Get statistics data from DB
        $statsQuery = $this->entityManager->createQueryBuilder()
            ->select([
                'SUM(gr.result) / SUM(g.rounds) AS bales',
                'COUNT(DISTINCT(gr.game)) AS gamesCount',
                sprintf('DATE_FORMAT(g.createdAt, \'%s\') AS gameDate', $this->getParam('database_date_format')),
            ])
            ->from(GameResult::class, 'gr')
            ->innerJoin('gr.game', 'g')
            ->andWhere('gr.strategy = :strategy')
            ->setParameter('strategy', $strategy)
            ->groupBy('gameDate')
        ;
        $dbStatistics = [
            'bales' => 0,
            'gamesCount' => 0,
        ];
        foreach ($statsQuery->getQuery()->getArrayResult() as $result) {
            $dbStatistics['bales'] += $formatter->toFloat($result['bales']);
            $dbStatistics['gamesCount'] += $formatter->toFloat($result['gamesCount']);
        }

        // 5. Calculate statistics that was returned from function
        $bales = 0;
        $gamesCount = 0;
        foreach ($statistics as $stats) {
            $bales += $stats['bales'];
            $gamesCount += $stats['gamesCount'];
        }

        // 6. Check is statistics calculated by service equal to statistics from DB
        $this->assertEquals($bales, $dbStatistics['bales'], sprintf('Test keys "%s" failed. Statistics for #%s strategy "bales" must have %s value but %s given',
            $testKeysID, $strategy->getId(), $bales, $dbStatistics['bales']));
        $this->assertEquals($gamesCount, $dbStatistics['gamesCount'], sprintf('Test keys "%s" failed. Statistics for #%s strategy "gamesCount" must have %s value but %s given',
            $testKeysID, $strategy->getId(), $bales, $dbStatistics['gamesCount']));
    }

    public function testStatisticsByRoundsCount()
    {
        $testKeysID = 'strategy_statistics_by_rounds_count';
        $formatter = $this->getFormatterService();

        // 1. Find random strategy with games statistics
        $strategy = $this->getRandomStrategy();

        // 2. Get strategy statistics
        $statistics = $this->getStatisticsService()->getStatisticsByRoundsCount($strategy);

        // 3. Check statistics data (must be an array and all elements must have all necessary attributes with correct types)
        $this->checkStatisticsData($statistics, $testKeysID, [
            'bales' => 'float',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
        ]);

        // 4. Get statistics data from DB
        $statsQuery = $this->entityManager->createQueryBuilder()
            ->select([
                'SUM(gr.result) / SUM(g.rounds) AS bales',
                'COUNT(DISTINCT(gr.game)) AS gamesCount',
                'g.rounds AS roundsCount',
            ])
            ->from(GameResult::class, 'gr')
            ->innerJoin('gr.game', 'g')
            ->andWhere('gr.strategy = :strategy')
            ->groupBy('roundsCount')
            ->setParameter('strategy', $strategy)
        ;
        $dbStatistics = [
            'bales' => 0,
            'gamesCount' => 0,
            'roundsCount' => 0,
        ];
        foreach ($statsQuery->getQuery()->getArrayResult() as $res) {
            $dbStatistics['bales'] += $formatter->toFloat($res['bales']);
            $dbStatistics['gamesCount'] += $formatter->toInt($res['gamesCount']);
            $dbStatistics['roundsCount'] += $formatter->toInt($res['roundsCount']);
        }

        // 5. Calculate statistics that was returned from function
        $bales = 0;
        $gamesCount = 0;
        $roundsCount = 0;
        foreach ($statistics as $stats) {
            $bales += $stats['bales'];
            $gamesCount += $stats['gamesCount'];
            $roundsCount += $stats['roundsCount'];
        }

        // 6. Check is statistics calculated by service equal to statistics from DB
        $this->assertEquals($bales, $dbStatistics['bales'], sprintf('Test keys "%s" failed. Statistics for #%s strategy "bales" must have %s value but %s given',
            $testKeysID, $strategy->getId(), $bales, $dbStatistics['bales']));
        $this->assertEquals($gamesCount, $dbStatistics['gamesCount'], sprintf('Test keys "%s" failed. Statistics for #%s strategy "gamesCount" must have %s value but %s given',
            $testKeysID, $strategy->getId(), $bales, $dbStatistics['gamesCount']));
        $this->assertEquals($roundsCount, $dbStatistics['roundsCount'], sprintf('Test keys "%s" failed. Statistics for #%s strategy "roundsCount" must have %s value but %s given',
            $testKeysID, $strategy->getId(), $bales, $dbStatistics['roundsCount']));
    }


    protected function getRandomStrategy(): Strategy
    {
        if ($this->randomStrategy !== null) {
            return $this->_randomStrategy;
        }
        $strategyRepository = $this->entityManager->getRepository(Strategy::class);
        $gameResultRepository = $this->entityManager->getRepository(GameResult::class);

        $strategiesIDsQuery = $gameResultRepository->createQueryBuilder('gr')
            ->select('s.id')
            ->innerJoin('gr.strategy', 's')
            ->setMaxResults(100)
        ;

        $ids = array_map(function ($result) { return intval($result['id']); }, $strategiesIDsQuery->getQuery()->getScalarResult());
        $faker = Factory::create();
        $id = $faker->randomElement($ids);

        return $this->randomStrategy = $strategyRepository->findOneBy(['id' => $id]);
    }

    protected function getStatisticsService(): StrategyStatisticsService
    {
        if ($this->statisticsService !== null) {
            return $this->statisticsService;
        }

        return $this->statisticsService = new StrategyStatisticsService($this->entityManager, self::$kernel->getContainer(), $this->getRepository(), $this->getFormatterService());
    }

    protected function getRepository(): StrategyStatisticsRepository
    {
        if ($this->repository !== null) {
            return $this->repository;
        }

        return $this->repository = new StrategyStatisticsRepository($this->entityManager, self::$kernel->getContainer());
    }
}