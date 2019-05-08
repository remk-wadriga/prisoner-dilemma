<?php

namespace App\Tests\Unit\Statistics;

use App\Entity\Game;
use App\Entity\GameResult;
use App\Entity\Strategy;
use App\Entity\User;
use App\Repository\Service\TotalStatisticsRepository;
use App\Service\Statistics\TotalStatisticsService;
use Faker\Factory;

class TotalStatisticsTest extends AbstractStatisticsUnitTestCase
{
    protected $statisticsService;
    protected $repository;
    protected $randomUser;

    public function testStatisticsByDates()
    {
        $testKeysID = 'total_statistics_by_dates';
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
        $statsQuery = $this->entityManager->createQueryBuilder()
            ->select([
                'SUM(gr.result) / SUM(g.rounds) AS bales',
                'COUNT(gr.game) AS gamesCount',
                'SUM(g.rounds) AS roundsCount',
                'g.createdAt AS gameDate',
            ])
            ->from(GameResult::class, 'gr')
            ->innerJoin('gr.game', 'g')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
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

    public function testStatisticsByStrategies()
    {
        $testKeysID = 'total_statistics_by_strategies';
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
                'COUNT(gr.game) AS gamesCount',
                'SUM(g.rounds) AS roundsCount',
                'SUM(gr.result) / SUM(g.rounds) AS bales',
            ])
            ->from(GameResult::class, 'gr')
            ->innerJoin('gr.game', 'g')
            ->innerJoin('gr.strategy', 's')
            ->andWhere('g.user = :user')
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
    }

    public function testStatisticsByRoundsCount()
    {
        $testKeysID = 'total_statistics_by_rounds_count';
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
        $statsQuery = $this->entityManager->createQueryBuilder()
            ->select([
                'SUM(gr.result) / SUM(g.rounds) AS bales',
                'COUNT(gr.game) AS gamesCount',
                'g.rounds AS roundsCount',
            ])
            ->from(GameResult::class, 'gr')
            ->innerJoin('gr.game', 'g')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
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

    public function testStatisticsByGames()
    {
        $testKeysID = 'total_statistics_by_games';
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
        $statsQuery = $this->entityManager->createQueryBuilder()
            ->select([
                'g.id AS gameID',
                'g.name AS game',
                'DATE_FORMAT(g.createdAt, \'%Y-%m-%d\') AS gameDate',
                'SUM(gr.result) AS totalBales',
                'SUM(gr.result) / g.rounds AS bales',
                'g.rounds AS roundsCount',
            ])
            ->from(GameResult::class, 'gr')
            ->innerJoin('gr.game', 'g')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
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


    protected function getRandomUser(): User
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

    protected function getStatisticsService(): TotalStatisticsService
    {
        if ($this->statisticsService !== null) {
            return $this->statisticsService;
        }

        return $this->statisticsService = new TotalStatisticsService($this->entityManager, self::$kernel->getContainer(), $this->getRepository(), $this->getFormatterService());
    }

    protected function getRepository(): TotalStatisticsRepository
    {
        if ($this->repository !== null) {
            return $this->repository;
        }

        return $this->repository = new TotalStatisticsRepository($this->entityManager, self::$kernel->getContainer());
    }
}