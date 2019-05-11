<?php

namespace App\Tests\Unit\Statistics;

use App\Entity\Game;
use App\Entity\GameResult;
use App\Entity\User;
use App\Repository\Service\TotalStatisticsRepository;
use App\Service\GameResultsService;
use App\Service\GameService;
use App\Service\Statistics\TotalStatisticsService;
use App\Service\StrategyDecisionsService;
use Faker\Factory;

class TotalStatisticsTest extends AbstractStatisticsUnitTestCase
{
    private $statisticsService;
    private $gameService;
    private $repository;

    public function testStatisticsDatesFilters()
    {
        $testKeysID = 'total_statistics_dates_filters';

        // 1. Get random user
        $user = $this->getRandomUser();

        // 2. Get dates
        $dates = $this->getStatisticsService()->getFirstAndLastGamesDates($user);

        // 3. Check params data (must be an array and all elements must have all necessary attributes with correct types)
        $this->checkStatisticsData([$dates], $testKeysID, [
            'start' => 'string',
            'end' => 'string',
        ]);

        // 4. Get "end" date from DB and check is service "and" date is equals to it
        $dbEndDate = $this->entityManager->createQueryBuilder()
            ->select('MAX(g.createdAt)')
            ->from(Game::class, 'g')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
        $this->assertNotEmpty($dbEndDate, sprintf('Test case "%s" failed. Last game date for user #%s not found', $testKeysID, $user->getId()));
        $this->assertEquals($dbEndDate, $dates['end'], sprintf('Test case "%s" failed. Max config date for user #%s must be equals to "%s" but "%s" given',
            $testKeysID, $user->getId(), $dbEndDate, $dates['end']));

        // 5. Convert dates from string to DateTime objects
        $startDate = new \DateTime($dates['start']);
        $endDate = new \DateTime($dates['end']);

        // 6. Check dates period - it must be equals to TotalStatisticsService.statisticsDatesPeriod value
        $modifiedStartDate = clone $startDate;
        $modifiedStartDate->modify($this->getStatisticsService()->statisticsDatesPeriod);
        $this->assertEquals($endDate, $modifiedStartDate, sprintf('Test case "%s" failed. The difference between "start" and "and" config dates must be equals to "%s" but it\'s not. Dates: "%s" - "%s"',
            $testKeysID, $this->getStatisticsService()->statisticsDatesPeriod, $startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')));
    }

    public function testStatisticsGameFiltersParams()
    {
        $testKeysID = 'total_statistics_games_params_filters';

        // 1. Get random user
        $user = $this->getRandomUser();

        // 2. Get game filters
        $oldFilters = $this->getGameService()->gamesFilters($user);

        // 3. Check params data (must be an array and all elements must have all necessary attributes with correct types)
        $this->checkGameParamsFilters($oldFilters, $testKeysID);

        // 4. Get user's games filters from DB
        $filters = $this->getUserGamesFilters();

        // 5. Compare the service's filters and filters form DB - they must be equals
        $this->assertEquals($filters, $oldFilters, sprintf('Tst %s failed. Game service returned the incorrect games filters values for user #%s',
            $testKeysID, $user->getId()));

        // 6. Create random filters array
        $randomFilters = [];
        $faker = Factory::create();
        foreach ($oldFilters as $filter => $values) {
            $randomFilters[$filter] = $faker->randomElement($values);
        }

        // 7. Enable doctrine filters
        $this->enableDoctrineFilters($randomFilters, 'game_');

        // 8. Get filters with enabled doctrine filters and check them
        $testKeysID = 'total_statistics_games_params_filters_filtered_by_random_game_params';
        $filters = $this->getGameService()->gamesFilters($user);
        $this->checkGameParamsFilters($filters, $testKeysID);

        // 9. Compare new "filtered filters" values with old "not filtered filters" values - them mustn't be equals
        $this->assertNotEquals($filters, $oldFilters, sprintf('Test %s failed. There is no difference between "filtered" and "not filtered" game params filters values. User: #%s, filterParams: %s',
            $testKeysID, $user->getId(), json_encode($randomFilters)));
    }

    public function testStatisticsByDates()
    {
        // 1. Check statistics without dates range
        $this->checkStatisticsByDates('total_statistics_by_dates');

        // 2. Enable Doctrine dates filters
        $this->enableDoctrineFilters($this->getRandomDatesPeriod());
        
        // 3. Check statistics with dates range
        $this->checkStatisticsByDates('total_statistics_by_dates_with_dates_range');
    }

    public function testStatisticsByStrategies()
    {
        // 1. Check statistics without dates range
        $this->checkStatisticsByStrategies('total_statistics_by_strategies');

        // 2. Enable Doctrine dates filters
        $this->enableDoctrineFilters($this->getRandomDatesPeriod());

        // 3. Check statistics with dates range
        $this->checkStatisticsByStrategies('total_statistics_by_strategies_with_dates_range');
    }

    public function testStatisticsByGames()
    {
        // 1. Check statistics without dates range
        $this->checkStatisticsByGames('total_statistics_by_games');

        // 2. Enable Doctrine dates filters
        $this->enableDoctrineFilters($this->getRandomDatesPeriod());

        // 3. Check statistics with dates range
        $this->checkStatisticsByGames('total_statistics_by_games_with_dates_range');
    }

    public function testStatisticsByRoundsCount()
    {
        // 1 Check statistics without dates range
        $this->checkStatisticsByRoundsCount('total_statistics_by_rounds_count');

        // 2. Enable Doctrine dates filters
        $this->enableDoctrineFilters($this->getRandomDatesPeriod());

        // 3. Check statistics with dates range
        $this->checkStatisticsByRoundsCount('total_statistics_by_rounds_count_with_dates_range');
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


    private function getStatisticsService(): TotalStatisticsService
    {
        if ($this->statisticsService !== null) {
            return $this->statisticsService;
        }

        return $this->statisticsService = new TotalStatisticsService($this->entityManager, self::$kernel->getContainer(), $this->getRepository(), $this->getFormatterService());
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

    private function getRepository(): TotalStatisticsRepository
    {
        if ($this->repository !== null) {
            return $this->repository;
        }

        return $this->repository = new TotalStatisticsRepository($this->entityManager, self::$kernel->getContainer());
    }

    private function createStatsQueryBuilder(User $user)
    {
        return $this->entityManager->createQueryBuilder()
            ->from(Game::class, 'g')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
        ;
    }

    private function createGameResultBalesSubQuery($alias = 'gr', $gameAlias = 'g')
    {
        return $this->entityManager->createQueryBuilder()
            ->from(GameResult::class, $alias)
            ->select(sprintf('SUM(%s.result)', $alias))
            ->andWhere(sprintf('%s.game = %s.id', $alias, $gameAlias))
        ;
    }

    private function getUserGamesFilters()
    {
        $games = $this->getRandomUser()->getGames();
        $filters = [
            'roundsCount' => [],
            'balesForWin' => [],
            'balesForLoos' => [],
            'balesForCooperation' => [],
            'balesForDraw' => [],
        ];
        foreach ($games as $game) {
            if (!in_array($game->getRounds(), $filters['roundsCount'])) {
                $filters['roundsCount'][] = $game->getRounds();
            }
            if (!in_array($game->getBalesForWin(), $filters['balesForWin'])) {
                $filters['balesForWin'][] = $game->getBalesForWin();
            }
            if (!in_array($game->getBalesForLoos(), $filters['balesForLoos'])) {
                $filters['balesForLoos'][] = $game->getBalesForLoos();
            }
            if (!in_array($game->getBalesForCooperation(), $filters['balesForCooperation'])) {
                $filters['balesForCooperation'][] = $game->getBalesForCooperation();
            }
            if (!in_array($game->getBalesForDraw(), $filters['balesForDraw'])) {
                $filters['balesForDraw'][] = $game->getBalesForDraw();
            }
        }
        return $filters;
    }

    private function checkGameParamsFilters($filters, $testKeysID)
    {
        $this->checkStatisticsData([$filters], $testKeysID, [
            'roundsCount' => 'array',
            'balesForWin' => 'array',
            'balesForLoos' => 'array',
            'balesForCooperation' => 'array',
            'balesForDraw' => 'array',
        ]);
    }


    private function checkStatisticsByDates($testKeysID)
    {
        $formatter = $this->getFormatterService();

        // 1. Get random user
        $user = $this->getRandomUser();

        // 2. Get statistics
        $statistics = $this->getStatisticsService()->getStatisticsByDates($user);

        // 3. Check statistics data (must be an array and all elements must have all necessary attributes with correct types)
        $this->checkStatisticsData($statistics, $testKeysID, [
            'bales' => 'float',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
            'gameDate' => 'string',
        ]);

        // 4. Get statistics data from DB
        $statsQuery = $this->createStatsQueryBuilder($user)
            ->select([
                sprintf('SUM_QUERY(%s)/SUM(g.rounds) AS bales', $this->createGameResultBalesSubQuery()->getQuery()->getDQL()),
                'COUNT(g) AS gamesCount',
                'SUM(g.rounds) AS roundsCount',
                sprintf('DATE_FORMAT(g.createdAt, \'%s\') AS gameDate', $this->getParam('database_date_format')),
            ])
            ->groupBy('gameDate')
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
        $this->assertEquals($bales, $dbStatistics['bales'], sprintf('Test keys "%s" failed. Statistics for #%s user statistics "bales" must have %s value but %s given',
            $testKeysID, $user->getId(), $bales, $dbStatistics['bales']));
        $this->assertEquals($gamesCount, $dbStatistics['gamesCount'], sprintf('Test keys "%s" failed. Statistics for #%s user statistics "gamesCount" must have %s value but %s given',
            $testKeysID, $user->getId(), $bales, $dbStatistics['gamesCount']));
        $this->assertEquals($roundsCount, $dbStatistics['roundsCount'], sprintf('Test keys "%s" failed. Statistics for #%s user statistics "roundsCount" must have %s value but %s given',
            $testKeysID, $user->getId(), $bales, $dbStatistics['roundsCount']));

        // 7. Create random game params filters and enable doctrine filters for them
        $randomFilters = $this->createRandomGameParamsFilters();
        $this->enableDoctrineFilters($randomFilters, 'game_');

        // 8. Get filtered statistics and check it
        $filteredStatistics = $this->getStatisticsService()->getStatisticsByDates($user);
        $this->checkStatisticsData($filteredStatistics, $testKeysID, [
            'bales' => 'float',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
            'gameDate' => 'string',
        ]);

        // 9. Compare filtered and not filtered statistics - they must be not equals
        if (empty($statistics) && empty($filteredStatistics)) {
            // "Not filtered" statistics is empty? That's mean that this is filtered by dates statistics, so we shouldn't compare them
            // if the filtered by game params statistics is empty too
            return;
        }
        $this->assertNotEquals($statistics, $filteredStatistics, sprintf('Test %s failed. Filtered and not filtered (by game params) statistics for user #%s data are equals',
            $testKeysID, $user->getId()));
    }

    private function checkStatisticsByStrategies($testKeysID)
    {
        $formatter = $this->getFormatterService();

        // 1. Get random user
        $user = $this->getRandomUser();

        // 2. Get statistics
        $statistics = $this->getStatisticsService()->getStatisticsByStrategies($user);

        // 3. Check statistics data (must be an array and all elements must have all necessary attributes with correct types)
        $this->checkStatisticsData($statistics, $testKeysID, [
            'strategy' => 'string',
            'bales' => 'float',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
        ]);

        // 4. Get statistics data from DB
        $statsQuery = $this->entityManager->createQueryBuilder()
            ->select([
                's.name AS strategy',
                'SUM(gr.result)/SUM(g.rounds) AS bales',
                'COUNT(gr.game) AS gamesCount',
                'SUM(g.rounds) AS roundsCount',
            ])
            ->from(GameResult::class, 'gr')
            ->innerJoin('gr.strategy', 's')
            ->innerJoin('gr.game', 'g')
            ->where('g.user = :user')
            ->setParameter('user', $user)
            ->groupBy('strategy')
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
        $this->assertEquals($bales, $dbStatistics['bales'], sprintf('Test keys "%s" failed. Statistics for #%s user statistics "bales" must have %s value but %s given',
            $testKeysID, $user->getId(), $bales, $dbStatistics['bales']));
        $this->assertEquals($gamesCount, $dbStatistics['gamesCount'], sprintf('Test keys "%s" failed. Statistics for #%s user statistics "gamesCount" must have %s value but %s given',
            $testKeysID, $user->getId(), $bales, $dbStatistics['gamesCount']));
        $this->assertEquals($roundsCount, $dbStatistics['roundsCount'], sprintf('Test keys "%s" failed. Statistics for #%s user statistics "roundsCount" must have %s value but %s given',
            $testKeysID, $user->getId(), $bales, $dbStatistics['roundsCount']));

        // 7. Create random game params filters and enable doctrine filters for them
        $randomFilters = $this->createRandomGameParamsFilters();
        $this->enableDoctrineFilters($randomFilters, 'game_');

        // 8. Get filtered statistics and check it
        $filteredStatistics = $this->getStatisticsService()->getStatisticsByStrategies($user);
        $this->checkStatisticsData($filteredStatistics, $testKeysID, [
            'strategy' => 'string',
            'bales' => 'float',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
        ]);

        // 9. Compare filtered and not filtered statistics - they must be not equals
        if (empty($statistics) && empty($filteredStatistics)) {
            // "Not filtered" statistics is empty? That's mean that this is filtered by dates statistics, so we shouldn't compare them
            // if the filtered by game params statistics is empty too
            return;
        }
        $this->assertNotEquals($statistics, $filteredStatistics, sprintf('Test %s failed. Filtered and not filtered (by game params) statistics for user #%s data are equals',
            $testKeysID, $user->getId()));
    }

    private function checkStatisticsByGames($testKeysID)
    {
        $formatter = $this->getFormatterService();

        // 1. Get random user
        $user = $this->getRandomUser();

        // 2. Get statistics
        $statistics = $this->getStatisticsService()->getStatisticsByGames($user);

        // 3. Check statistics data (must be an array and all elements must have all necessary attributes with correct types)
        $this->checkStatisticsData($statistics, $testKeysID, [
            'game' => 'string',
            'gameDate' => 'string',
            'totalBales' => 'integer',
            'bales' => 'double',
            'roundsCount' => 'integer',
            'winner' => 'array',
            'loser' => 'array',
        ]);

        // 4. Get statistics data from DB
        $statsQuery = $this->createStatsQueryBuilder($user)
            ->select([
                'g.id AS gameID',
                'g.name AS game',
                sprintf('DATE_FORMAT(g.createdAt, \'%s\') AS gameDate', $this->getParam('database_date_format')),
                sprintf('SUM_QUERY(%s)/g.rounds AS bales', $this->createGameResultBalesSubQuery('gr1')->getQuery()->getDQL()),
                sprintf('SUM_QUERY(%s) AS totalBales', $this->createGameResultBalesSubQuery('gr2')->getQuery()->getDQL()),
                'g.rounds AS roundsCount',
            ])
            ->groupBy('gameID')
            ->addGroupBy('game')
        ;
        $dbStatisticsResults = $statsQuery->getQuery()->getArrayResult();

        $dbStatistics = [
            'totalBales' => 0,
            'bales' => 0,
            'roundsCount' => 0,
        ];
        foreach ($dbStatisticsResults as $res) {
            $dbStatistics['totalBales'] += $formatter->toInt($res['totalBales']);
            $dbStatistics['bales'] += $formatter->toFloat($res['bales']);
            $dbStatistics['roundsCount'] += $formatter->toInt($res['roundsCount']);
        }

        // 5. Calculate statistics that was returned from function
        $totalBales = 0;
        $bales = 0;
        $roundsCount = 0;
        $winners = [];
        $losers = [];
        foreach ($statistics as $index => $stats) {
            $totalBales += $stats['totalBales'];
            $bales += $stats['bales'];
            $roundsCount += $stats['roundsCount'];
            if (gettype($stats['winner']['bales']) === 'integer') {
                $stats['winner']['bales'] = floatval($stats['winner']['bales']);
            }
            if (gettype($stats['loser']['bales']) === 'integer') {
                $stats['loser']['bales'] = floatval($stats['loser']['bales']);
            }
            $winners[] = $stats['winner'];
            $losers[] = $stats['loser'];

            $statistics[$stats['game']] = $stats;
            unset($statistics[$index]);
        }

        // 6. Check is statistics calculated by service equal to statistics from DB
        $this->assertEquals($totalBales, $dbStatistics['totalBales'], sprintf('Test keys "%s" failed. Statistics for #%s user statistics "totalBales" must have %s value but %s given',
            $testKeysID, $user->getId(), $totalBales, $dbStatistics['totalBales']));
        $this->assertEquals($bales, $dbStatistics['bales'], sprintf('Test keys "%s" failed. Statistics for #%s user statistics "bales" must have %s value but %s given',
            $testKeysID, $user->getId(), $bales, $dbStatistics['bales']));
        $this->assertEquals($roundsCount, $dbStatistics['roundsCount'], sprintf('Test keys "%s" failed. Statistics for #%s user statistics "roundsCount" must have %s value but %s given',
            $testKeysID, $user->getId(), $roundsCount, $dbStatistics['roundsCount']));

        // 7. Check loser and winner of game
        $this->checkStatisticsData($winners, $testKeysID, ['strategy' => 'string', 'bales' => 'double']);
        $this->checkStatisticsData($losers, $testKeysID, ['strategy' => 'string', 'bales' => 'double']);

        // 8. Check is loser and winner have correct values
        $gameRepository = $this->entityManager->getRepository(Game::class);
        $gameResultsRepository = $this->entityManager->getRepository(GameResult::class);
        foreach ($dbStatisticsResults as $dbStats) {
            // Find game by ID
            $game = $gameRepository->findOneBy(['id' => $dbStats['gameID']]);
            $this->assertNotEmpty($game, sprintf('Test keys "%s" failed. Game #%s not found', $testKeysID, $dbStats['gameID']));

            // Check is stats has the same index element
            $this->assertArrayHasKey($game->getName(), $statistics, sprintf('Test keys "%s" failed. Statistics for user #%s has no index %s',
                $testKeysID, $user->getId(), $game->getName()));

            // Get stats element
            $stats = $statistics[$game->getName()];

            // Get DB winner and loser
            $dbGameWinner = $gameResultsRepository->findGameBestResult($game);
            $dbGameLoser = $gameResultsRepository->findGameWorseResult($game);

            // Calculate average DB winner and loser bales
            $dbGameWinner['bales'] = $formatter->toFloat($dbGameWinner['bales'] / $stats['roundsCount']);
            $dbGameLoser['bales'] = $formatter->toFloat($dbGameLoser['bales'] / $stats['roundsCount']);

            // Check is loser and winner have correct values
            $this->assertEquals($dbGameWinner, $stats['winner'], sprintf('Test keys "%s" failed. Statistics returns an incorrect winner. Winner of game #%s must be %s, but %s given',
                $testKeysID, $game->getId(), json_encode($dbGameWinner), json_encode($stats['winner'])));
            $this->assertEquals($dbGameLoser, $stats['loser'], sprintf('Test keys "%s" failed. Statistics returns an incorrect winner. Winner of game #%s must be %s, but %s given',
                $testKeysID, $game->getId(), json_encode($dbGameLoser), json_encode($stats['loser'])));
        }

        // 9. Create random game params filters and enable doctrine filters for them
        $randomFilters = $this->createRandomGameParamsFilters();
        $this->enableDoctrineFilters($randomFilters, 'game_');

        // 10. Get filtered statistics and check it
        $filteredStatistics = $this->getStatisticsService()->getStatisticsByGames($user);
        $this->checkStatisticsData($filteredStatistics, $testKeysID, [
            'game' => 'string',
            'gameDate' => 'string',
            'totalBales' => 'integer',
            'bales' => 'double',
            'roundsCount' => 'integer',
            'winner' => 'array',
            'loser' => 'array',
        ]);

        // 11. Compare filtered and not filtered statistics - they must be not equals
        if (empty($statistics) && empty($filteredStatistics)) {
            // "Not filtered" statistics is empty? That's mean that this is filtered by dates statistics, so we shouldn't compare them
            // if the filtered by game params statistics is empty too
            return;
        }
        $this->assertNotEquals($statistics, $filteredStatistics, sprintf('Test %s failed. Filtered and not filtered (by game params) statistics for user #%s data are equals',
            $testKeysID, $user->getId()));
    }

    private function checkStatisticsByRoundsCount($testKeysID)
    {
        $formatter = $this->getFormatterService();

        // 1. Get random user
        $user = $this->getRandomUser();

        // 2. Get statistics
        $statistics = $this->getStatisticsService()->getStatisticsByRoundsCount($user);

        // 3. Check statistics data (must be an array and all elements must have all necessary attributes with correct types)
        $this->checkStatisticsData($statistics, $testKeysID, [
            'bales' => 'float',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
        ]);

        // 4. Get statistics data from DB
        $statsQuery = $this->createStatsQueryBuilder($user)
            ->select([
                sprintf('SUM_QUERY(%s)/SUM(g.rounds) AS bales', $this->createGameResultBalesSubQuery()->getQuery()->getDQL()),
                'COUNT(g) AS gamesCount',
                'g.rounds AS roundsCount',
            ])
            ->groupBy('roundsCount')
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
        $this->assertEquals($bales, $dbStatistics['bales'], sprintf('Test keys "%s" failed. Statistics for #%s user statistics "bales" must have %s value but %s given',
            $testKeysID, $user->getId(), $bales, $dbStatistics['bales']));
        $this->assertEquals($gamesCount, $dbStatistics['gamesCount'], sprintf('Test keys "%s" failed. Statistics for #%s user statistics "gamesCount" must have %s value but %s given',
            $testKeysID, $user->getId(), $bales, $dbStatistics['gamesCount']));
        $this->assertEquals($roundsCount, $dbStatistics['roundsCount'], sprintf('Test keys "%s" failed. Statistics for #%s user statistics "roundsCount" must have %s value but %s given',
            $testKeysID, $user->getId(), $bales, $dbStatistics['roundsCount']));

        // 7. Create random game params filters and enable doctrine filters for them
        $randomFilters = $this->createRandomGameParamsFilters();
        $this->enableDoctrineFilters($randomFilters, 'game_');

        // 8. Get filtered statistics and check it
        $filteredStatistics = $this->getStatisticsService()->getStatisticsByRoundsCount($user);
        $this->checkStatisticsData($filteredStatistics, $testKeysID, [
            'bales' => 'float',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
        ]);

        // 9. Compare filtered and not filtered statistics - they must be not equals
        if (empty($statistics) && empty($filteredStatistics)) {
            // "Not filtered" statistics is empty? That's mean that this is filtered by dates statistics, so we shouldn't compare them
            // if the filtered by game params statistics is empty too
            return;
        }
        $this->assertNotEquals($statistics, $filteredStatistics, sprintf('Test %s failed. Filtered and not filtered (by game params) statistics for user #%s data are equals',
            $testKeysID, $user->getId()));
    }
}