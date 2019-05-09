<?php

namespace App\Tests\Unit\Statistics;

use App\Entity\Game;
use App\Entity\GameResult;
use App\Entity\User;
use App\Repository\Service\TotalStatisticsRepository;
use App\Service\Statistics\TotalStatisticsService;
use Faker\Factory;

class TotalStatisticsTest extends AbstractStatisticsUnitTestCase
{
    private $statisticsService;
    private $repository;
    private $randomUser;

    public function testStatisticsDatesParams()
    {
        $testKeysID = 'total_statistics_dates_format';

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

    public function testStatisticsByDates()
    {
        // 1. Check statistics without dates range
        $this->checkStatisticsByDates('total_statistics_by_dates');

        // 2. Check statistics with dates range
        $this->checkStatisticsByDates('total_statistics_by_dates_with_dates_range', $this->getRandomDatesPeriod());
    }

    public function testStatisticsByStrategies()
    {
        // 1. Check statistics without dates range
        $this->checkStatisticsByStrategies('total_statistics_by_strategies');

        // 2. Check statistics with dates range
        $this->checkStatisticsByStrategies('total_statistics_by_strategies_with_dates_range', $this->getRandomDatesPeriod());
    }

    public function testStatisticsByRoundsCount()
    {
        // 1 Check statistics without dates range
        $this->checkStatisticsByRoundsCount('total_statistics_by_rounds_count');

        // 2. Check statistics with dates range
        $this->checkStatisticsByRoundsCount('total_statistics_by_rounds_count_with_dates_range', $this->getRandomDatesPeriod());
    }

    public function testStatisticsByGames()
    {
        // 1. Check statistics without dates range
        $this->checkStatisticsByGames('total_statistics_by_games');

        // 2. Check statistics with dates range
        $this->checkStatisticsByGames('total_statistics_by_games_with_dates_range', $this->getRandomDatesPeriod());
    }


    private function getRandomUser(): User
    {
        if ($this->randomUser !== null) {
            return $this->randomUser;
        }
        $userRepository = $this->entityManager->getRepository(User::class);
        $gameResultRepository = $this->entityManager->getRepository(GameResult::class);

        $strategiesIDsQuery = $gameResultRepository->createQueryBuilder('gr')
            ->select('u.id')
            ->innerJoin('gr.strategy', 's')
            ->innerJoin('s.user', 'u')
            ->setMaxResults(100)
        ;

        $ids = array_map(function ($result) { return intval($result['id']); }, $strategiesIDsQuery->getQuery()->getScalarResult());
        $faker = Factory::create();
        $id = $faker->randomElement($ids);

        return $this->randomUser = $userRepository->findOneBy(['id' => $id]);
    }

    private function getStatisticsService(): TotalStatisticsService
    {
        if ($this->statisticsService !== null) {
            return $this->statisticsService;
        }

        return $this->statisticsService = new TotalStatisticsService($this->entityManager, self::$kernel->getContainer(), $this->getRepository(), $this->getFormatterService());
    }

    private function getRepository(): TotalStatisticsRepository
    {
        if ($this->repository !== null) {
            return $this->repository;
        }

        return $this->repository = new TotalStatisticsRepository($this->entityManager, self::$kernel->getContainer());
    }

    private function getRandomDatesPeriod()
    {
        $dates = $this->getStatisticsService()->getFirstAndLastGamesDates($this->getRandomUser());
        $faker = Factory::create();
        $dates['toDate'] = (new \DateTime($dates['end']))
            ->modify(sprintf('-%s days', $faker->numberBetween(0, 5)))
            ->format($this->getParam('backend_date_format'));
        $dates['fromDate'] = (new \DateTime($dates['toDate']))
            ->modify(sprintf('-%s days', $faker->numberBetween(1, 10)))
            ->format($this->getParam('backend_date_format'));
        unset($dates['start'], $dates['end']);
        return $dates;
    }

    private function createStatsQueryBuilderWithJoinedGameFilteredByDates(User $user, array $datesRange)
    {
        $statsQuery = $this->entityManager->createQueryBuilder()
            ->from(GameResult::class, 'gr')
            ->innerJoin('gr.game', 'g')
            ->andWhere('g.user = :user')
        ;

        $parameters = ['user' => $user];

        if (!empty($datesRange)) {
            $statsQuery->andWhere('g.createdAt > :from_date AND g.createdAt < :to_date');
            $parameters['from_date'] = new \DateTime($datesRange['fromDate']);
            $parameters['to_date'] = (new \DateTime($datesRange['toDate']))->modify('1 days');
        }

        $statsQuery->setParameters($parameters);

        return $statsQuery;
    }


    private function checkStatisticsByDates($testKeysID, array $datesRange = [])
    {
        $formatter = $this->getFormatterService();

        // 1. Get random user
        $user = $this->getRandomUser();

        // 2. Get statistics
        $this->getStatisticsService()->filters = $datesRange;
        $statistics = $this->getStatisticsService()->getStatisticsByDates($user);

        // 3. Check statistics data (must be an array and all elements must have all necessary attributes with correct types)
        $this->checkStatisticsData($statistics, $testKeysID, [
            'bales' => 'float',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
            'gameDate' => 'string',
        ]);

        // 4. Get statistics data from DB
        $statsQuery = $this->createStatsQueryBuilderWithJoinedGameFilteredByDates($user, $datesRange)
            ->select([
                'SUM(gr.result) / SUM(g.rounds) AS bales',
                'COUNT(gr.game) AS gamesCount',
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
    }

    private function checkStatisticsByStrategies($testKeysID, array $datesRange = [])
    {
        $formatter = $this->getFormatterService();

        // 1. Get random user
        $user = $this->getRandomUser();

        // 2. Get statistics
        $this->getStatisticsService()->filters = $datesRange;
        $statistics = $this->getStatisticsService()->getStatisticsByStrategies($user);

        // 3. Check statistics data (must be an array and all elements must have all necessary attributes with correct types)
        $this->checkStatisticsData($statistics, $testKeysID, [
            'strategy' => 'string',
            'bales' => 'float',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
        ]);

        // 4. Get statistics data from DB
        $statsQuery = $this->createStatsQueryBuilderWithJoinedGameFilteredByDates($user, $datesRange)
            ->select([
                's.name AS strategy',
                'COUNT(gr.game) AS gamesCount',
                'SUM(g.rounds) AS roundsCount',
                'SUM(gr.result) / SUM(g.rounds) AS bales',
            ])
            ->innerJoin('gr.strategy', 's')
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
    }

    private function checkStatisticsByRoundsCount($testKeysID, array $datesRange = [])
    {
        $formatter = $this->getFormatterService();

        // 1. Get random user
        $user = $this->getRandomUser();

        // 2. Get statistics
        $this->getStatisticsService()->filters = $datesRange;
        $statistics = $this->getStatisticsService()->getStatisticsByRoundsCount($user);

        // 3. Check statistics data (must be an array and all elements must have all necessary attributes with correct types)
        $this->checkStatisticsData($statistics, $testKeysID, [
            'bales' => 'float',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
        ]);

        // 4. Get statistics data from DB
        $statsQuery = $this->createStatsQueryBuilderWithJoinedGameFilteredByDates($user, $datesRange)
            ->select([
                'SUM(gr.result) / SUM(g.rounds) AS bales',
                'COUNT(gr.game) AS gamesCount',
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
    }

    private function checkStatisticsByGames($testKeysID, array $datesRange = [])
    {
        $formatter = $this->getFormatterService();

        // 1. Get random user
        $user = $this->getRandomUser();

        // 2. Get statistics
        $this->getStatisticsService()->filters = $datesRange;
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
        $statsQuery = $this->createStatsQueryBuilderWithJoinedGameFilteredByDates($user, $datesRange)
            ->select([
                'g.id AS gameID',
                'g.name AS game',
                'DATE_FORMAT(g.createdAt, \'%Y-%m-%d\') AS gameDate',
                'SUM(gr.result) AS totalBales',
                'SUM(gr.result) / g.rounds AS bales',
                'g.rounds AS roundsCount',
            ])
            ->groupBy('gameID')
            ->addGroupBy('game')
            ->addGroupBy('gameDate')
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

        // 6. Check is loser and winner have correct values
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
    }
}