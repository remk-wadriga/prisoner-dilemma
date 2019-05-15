<?php

namespace App\Tests\Unit\Statistics;

use App\Entity\Game;
use App\Entity\GameResult;
use App\Entity\Strategy;
use App\Repository\Service\StrategyStatisticsRepository;
use App\Service\GameResultsService;
use App\Service\GameService;
use App\Service\Statistics\StrategyStatisticsService;
use App\Service\StrategyDecisionsService;
use Faker\Factory;

class StrategyStatisticsTest extends AbstractStatisticsUnitTestCase
{
    private $statisticsService;
    private $gameService;
    private $repository;
    private $randomStrategy;

    public function testStatisticsDatesParams()
    {
        $testKeysID = 'strategy_statistics_dates_format';

        // 1. Find random strategy with games statistics
        $strategy = $this->getRandomStrategy();

        // 2. Get dates
        $dates = $this->getStatisticsService()->getFirstAndLastGamesDates($strategy);

        // 3. Check params data (must be an array and all elements must have all necessary attributes with correct types)
        $this->checkStatisticsData([$dates], $testKeysID, [
            'start' => 'string',
            'end' => 'string',
        ]);

        // 4. Get "end" date from DB and check is service "and" date is equals to it
        $dbEndDate = $this->entityManager->createQueryBuilder()
            ->select('MAX(g.createdAt) AS end')
            ->from(GameResult::class, 'gr')
            ->innerJoin('gr.game', 'g')
            ->andWhere('gr.strategy = :strategy')
            ->setParameter('strategy', $strategy)
            ->getQuery()
            ->getSingleScalarResult();
        $this->assertNotEmpty($dbEndDate, sprintf('Test case "%s" failed. Last game date for strategy #%s not found', $testKeysID, $strategy->getId()));
        $this->assertEquals($dbEndDate, $dates['end'], sprintf('Test case "%s" failed. Max config date for strategy #%s must be equals to "%s" but "%s" given',
            $testKeysID, $strategy->getId(), $dbEndDate, $dates['end']));

        // 5. Convert dates from string to DateTime objects
        $startDate = new \DateTime($dates['start']);
        $endDate = new \DateTime($dates['end']);

        // 6. Check dates period - it must be equals to TotalStatisticsService.statisticsDatesPeriod value
        $modifiedStartDate = clone $startDate;
        $modifiedStartDate->modify($this->getStatisticsService()->statisticsDatesPeriod);
        $this->assertEquals($endDate, $modifiedStartDate, sprintf('Test case "%s" failed. The difference between "start" and "and" config dates must be equals to "%s" but it\'s not. Dates: "%s" - "%s"',
            $testKeysID, $this->getStatisticsService()->statisticsDatesPeriod, $startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')));
    }


    public function testStatisticsByDates()
    {
        // 1. Check statistics without dates range
        $this->checkStatisticsByDates('strategy_statistics_by_dates');

        // 2. Enable Doctrine dates filters
        $this->enableDoctrineFilters($this->getRandomDatesPeriod());

        // 3. Check statistics with dates range
        $this->checkStatisticsByDates('strategy_statistics_by_dates_with_dates_range');
    }

    public function testStatisticsByDatesFilteredByDates()
    {
        // 1. Find random strategy with games statistics
        $strategy = $this->getRandomStrategy();
        $formatter = $this->getFormatterService();
        $randomDatesPeriod = $this->getRandomDatesPeriod();

        // 2. Get filtered by dates statistics from DB
        $statisticsFromDBQuery = $this->entityManager->createQueryBuilder()
            ->select([
                'SUM(gr.result)/SUM(g.rounds) AS bales',
                'COUNT(g) AS gamesCount',
                sprintf('DATE_FORMAT(g.createdAt, \'%s\') AS gameDate', $this->getParam('database_date_format')),
            ])
            ->from(Game::class, 'g')
            ->innerJoin('g.gameResults', 'gr')
            ->andWhere('gr.strategy = :strategy')
            ->setParameter('strategy', $strategy)
            ->groupBy('gameDate')
        ;
        $this->addFiltersToQuery($statisticsFromDBQuery, $randomDatesPeriod);
        $statisticsFromDB = [];
        foreach ($statisticsFromDBQuery->getQuery()->getArrayResult() as $stats) {
            $statisticsFromDB[] = array_merge($stats, [
                'bales' => $formatter->toFloat($stats['bales']),
                'gamesCount' => $formatter->toInt($stats['gamesCount']),
            ]);
        }

        // 3. Enable Doctrine dates filters
        $this->enableDoctrineFilters($randomDatesPeriod);

        // 4. Get filtered statistics from Service
        $statisticsFromService = $this->getStatisticsService()->getStatisticsByDates($strategy);

        // 5. Compare statistics from DB and statistics from service - they must be equals
        $this->assertEquals($statisticsFromDB, $statisticsFromService, sprintf('Test %s failed. Filtered statistics from DB and from service are not much. Filters: %s',
            'strategy_statistics_by_dates_filtered_by_dates', json_encode($randomDatesPeriod)));
    }

    public function testStatisticsByDatesFilteredByGameParams()
    {
        // 1. Find random strategy and get random game params filter
        $strategy = $this->getRandomStrategy();
        $formatter = $this->getFormatterService();
        $gameParamsFilters = $this->createRandomGameParamsFilters();
        $faker = Factory::create();
        $filtersKeys = array_keys($gameParamsFilters);
        $randomKey1 = $faker->randomElement($filtersKeys);
        $randomKey2 = $faker->randomElement($filtersKeys);
        $randomKey3 = $faker->randomElement($filtersKeys);
        unset($gameParamsFilters[$randomKey1], $gameParamsFilters[$randomKey2], $gameParamsFilters[$randomKey3]);

        // 2. Get filtered by dates statistics from DB
        $statisticsFromDBQuery = $this->entityManager->createQueryBuilder()
            ->select([
                'SUM(gr.result)/SUM(g.rounds) AS bales',
                'COUNT(g) AS gamesCount',
                sprintf('DATE_FORMAT(g.createdAt, \'%s\') AS gameDate', $this->getParam('database_date_format')),
            ])
            ->from(Game::class, 'g')
            ->innerJoin('g.gameResults', 'gr')
            ->andWhere('gr.strategy = :strategy')
            ->setParameter('strategy', $strategy)
            ->groupBy('gameDate')
        ;
        $this->addFiltersToQuery($statisticsFromDBQuery, $gameParamsFilters);
        $statisticsFromDB = [];
        foreach ($statisticsFromDBQuery->getQuery()->getArrayResult() as $stats) {
            $statisticsFromDB[] = array_merge($stats, [
                'bales' => $formatter->toFloat($stats['bales']),
                'gamesCount' => $formatter->toInt($stats['gamesCount']),
            ]);
        }

        // 3. Enable Doctrine dates filters
        $this->enableDoctrineFilters($gameParamsFilters, 'game_');

        // 4. Get filtered statistics from Service
        $statisticsFromService = $this->getStatisticsService()->getStatisticsByDates($strategy);

        // 5. Compare statistics from DB and statistics from service - they must be equals
        $this->assertEquals($statisticsFromDB, $statisticsFromService, sprintf('Test %s failed. Filtered statistics from DB and from service are not much. Filters: %s',
            'strategy_statistics_by_dates_filtered_by_game_params', json_encode($gameParamsFilters)));
    }


    public function testStatisticsByRoundsCount()
    {
        // 1. Check statistics without dates range
        $this->checkStatisticsByRoundsCount('strategy_statistics_by_rounds_count');

        // 2. Enable Doctrine dates filters
        $this->enableDoctrineFilters($this->getRandomDatesPeriod());

        // 3. Check statistics with dates range
        $this->checkStatisticsByRoundsCount('strategy_statistics_by_rounds_count_with_dates_range');
    }

    public function testStatisticsByRoundsCountFilteredByDates()
    {
        // 1. Find random strategy with games statistics
        $strategy = $this->getRandomStrategy();
        $formatter = $this->getFormatterService();
        $randomDatesPeriod = $this->getRandomDatesPeriod();

        // 2. Get filtered by dates statistics from DB
        $statisticsFromDBQuery = $this->entityManager->createQueryBuilder()
            ->select([
                'SUM(gr.result)/SUM(g.rounds) AS bales',
                'COUNT(g) AS gamesCount',
                'g.rounds AS roundsCount',
            ])
            ->from(Game::class, 'g')
            ->innerJoin('g.gameResults', 'gr')
            ->andWhere('gr.strategy = :strategy')
            ->groupBy('roundsCount')
            ->setParameter('strategy', $strategy)
        ;
        $this->addFiltersToQuery($statisticsFromDBQuery, $randomDatesPeriod);
        $statisticsFromDB = [];
        foreach ($statisticsFromDBQuery->getQuery()->getArrayResult() as $stats) {
            $statisticsFromDB[] = array_merge($stats, [
                'bales' => $formatter->toFloat($stats['bales']),
                'gamesCount' => $formatter->toInt($stats['gamesCount']),
            ]);
        }

        // 3. Enable Doctrine dates filters
        $this->enableDoctrineFilters($randomDatesPeriod);

        // 4. Get filtered statistics from Service
        $statisticsFromService = $this->getStatisticsService()->getStatisticsByRoundsCount($strategy);

        // 5. Compare statistics from DB and statistics from service - they must be equals
        $this->assertEquals($statisticsFromDB, $statisticsFromService, sprintf('Test %s failed. Filtered statistics from DB and from service are not much. Filters: %s',
            'strategy_statistics_by_rounds_count_filtered_by_dates', json_encode($randomDatesPeriod)));
    }

    public function testStatisticsByRoundsCountFilteredByGameParams()
    {
        // 1. Find random strategy and get random game params filter
        $strategy = $this->getRandomStrategy();
        $formatter = $this->getFormatterService();
        $gameParamsFilters = $this->createRandomGameParamsFilters();
        $faker = Factory::create();
        $filtersKeys = array_keys($gameParamsFilters);
        $randomKey1 = $faker->randomElement($filtersKeys);
        $randomKey2 = $faker->randomElement($filtersKeys);
        $randomKey3 = $faker->randomElement($filtersKeys);
        unset($gameParamsFilters[$randomKey1], $gameParamsFilters[$randomKey2], $gameParamsFilters[$randomKey3]);

        // 2. Get filtered by dates statistics from DB
        $statisticsFromDBQuery = $this->entityManager->createQueryBuilder()
            ->select([
                'SUM(gr.result)/SUM(g.rounds) AS bales',
                'COUNT(g) AS gamesCount',
                'g.rounds AS roundsCount',
            ])
            ->from(Game::class, 'g')
            ->innerJoin('g.gameResults', 'gr')
            ->andWhere('gr.strategy = :strategy')
            ->groupBy('roundsCount')
            ->setParameter('strategy', $strategy)
        ;
        $this->addFiltersToQuery($statisticsFromDBQuery, $gameParamsFilters);
        $statisticsFromDB = [];
        foreach ($statisticsFromDBQuery->getQuery()->getArrayResult() as $stats) {
            $statisticsFromDB[] = array_merge($stats, [
                'bales' => $formatter->toFloat($stats['bales']),
                'gamesCount' => $formatter->toInt($stats['gamesCount']),
            ]);
        }

        // 3. Enable Doctrine dates filters
        $this->enableDoctrineFilters($gameParamsFilters, 'game_');

        // 4. Get filtered statistics from Service
        $statisticsFromService = $this->getStatisticsService()->getStatisticsByRoundsCount($strategy);

        // 5. Compare statistics from DB and statistics from service - they must be equals
        $this->assertEquals($statisticsFromDB, $statisticsFromService, sprintf('Test %s failed. Filtered statistics from DB and from service are not much. Filters: %s',
            'strategy_statistics_by_rounds_count_filtered_by_game_params', json_encode($gameParamsFilters)));
    }


    protected function createRandomGameParamsFilters()
    {
        $faker = Factory::create();
        $filters = $this->getGameService()->gamesFilters($this->getRandomUser());
        $randomFilters = [];
        foreach ($filters as $filter => $values) {
            $randomFilters[$filter] = $faker->randomElement($values);
        }
        return $randomFilters;
    }


    private function getRandomStrategy(): Strategy
    {
        if ($this->randomStrategy !== null) {
            return $this->randomStrategy;
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

    private function getStatisticsService(): StrategyStatisticsService
    {
        if ($this->statisticsService !== null) {
            return $this->statisticsService;
        }

        return $this->statisticsService = new StrategyStatisticsService($this->entityManager, self::$kernel->getContainer(), $this->getRepository(), $this->getFormatterService());
    }

    private function getGameService(): GameService
    {
        if ($this->gameService !== null) {
            return $this->gameService;
        }
        $decisionService = new StrategyDecisionsService($this->entityManager, static::$container);
        $gameResultsService = new GameResultsService($this->entityManager, static::$container);
        return $this->gameService = new GameService($this->entityManager, $decisionService, $gameResultsService, static::$container);
    }

    private function getRepository(): StrategyStatisticsRepository
    {
        if ($this->repository !== null) {
            return $this->repository;
        }

        return $this->repository = new StrategyStatisticsRepository($this->entityManager, self::$kernel->getContainer());
    }


    private function checkStatisticsByDates($testKeysID)
    {
        // 1. Find random strategy with games statistics
        $strategy = $this->getRandomStrategy();
        $formatter = $this->getFormatterService();

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
                'SUM(gr.result)/SUM(g.rounds) AS bales',
                'COUNT(g) AS gamesCount',
                sprintf('DATE_FORMAT(g.createdAt, \'%s\') AS gameDate', $this->getParam('database_date_format')),
            ])
            ->from(Game::class, 'g')
            ->innerJoin('g.gameResults', 'gr')
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

    private function checkStatisticsByRoundsCount($testKeysID)
    {
        // 1. Find random strategy with games statistics
        $strategy = $this->getRandomStrategy();
        $formatter = $this->getFormatterService();

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
                'SUM(gr.result)/SUM(g.rounds) AS bales',
                'COUNT(g) AS gamesCount',
                'g.rounds AS roundsCount',
            ])
            ->from(Game::class, 'g')
            ->innerJoin('g.gameResults', 'gr')
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
}