<?php

namespace App\Tests\Web\Statistics;

use App\Repository\Service\TotalStatisticsRepository;
use Faker\Factory;

class TotalStatisticsTest extends AbstractStatisticsApiTestCase
{
    private $repository;


    public function testStatisticsByDates()
    {
        // 1. Login as user
        $this->logInAsUser();

        // 2. Get total statistics without dates range and check it
        $this->checkStatisticsByDates('total_statics_by_dates');

        // 3. Get total statistics with dates range and check it
        $this->checkStatisticsByDates('total_statics_by_dates_with_dates', $this->getRandomDatesPeriod());
    }

    public function testStatisticsByStrategies()
    {
        // 1. Login as user
        $this->logInAsUser();

        // 2. Get total statistics without dates range and check it
        $this->checkStatisticsByStrategies('total_statics_by_strategies');

        // 3. Get total statistics with dates range and check it
        $this->checkStatisticsByStrategies('total_statics_by_strategies_with_dates', $this->getRandomDatesPeriod());
    }

    public function testStatisticsByGames()
    {
        // 1. Login as user
        $this->logInAsUser();

        // 2. Get total statistics without dates range and check it
        $this->checkStatisticsByGames('total_statics_by_games');

        // 3. Get total statistics with dates range and check it
        $this->checkStatisticsByGames('total_statics_by_games_with_dates', $this->getRandomDatesPeriod());
    }

    public function testStatisticsByRoundsCount()
    {
        // 1. Login as user
        $this->logInAsUser();

        // 2. Get total statistics without dates range and check it
        $this->checkStatisticsByRoundsCount('total_statics_by_rounds_count');

        // 3. Get total statistics with dates range and check it
        $this->checkStatisticsByRoundsCount('total_statics_by_rounds_count_with_dates', $this->getRandomDatesPeriod());
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
        $dates = $this->getRepository()->getFirstAndLastGamesDates($this->user);

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


    private function checkStatisticsByDates(string $testKeysID, array $datesPeriod = [])
    {
        $response = $this->request('total_statistics_by_dates', $datesPeriod);
        $this->checkStatisticsResponse($response, $testKeysID, [
            'bales' => 'double',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
            'gameDate' => 'string',
        ]);
    }

    private function checkStatisticsByStrategies(string $testKeysID, array $datesPeriod = [])
    {
        $response = $this->request('total_statistics_by_strategies', $datesPeriod);
        $this->checkStatisticsResponse($response, $testKeysID, [
            'strategy' => 'string',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
            'bales' => ['double', 'integer'],
        ]);
    }

    private function checkStatisticsByGames(string $testKeysID, array $datesPeriod = [])
    {
        // 1. Get total statistics and check it
        $response = $this->request('total_statistics_by_games', $datesPeriod);
        $this->checkStatisticsResponse($response, $testKeysID, [
            'game' => 'string',
            'gameDate' => 'string',
            'totalBales' => 'integer',
            'bales' => ['double', 'integer'],
            'roundsCount' => 'integer',
            'winner' => 'array',
            'loser' => 'array',
        ]);

        // 3. Get game winner and loser and check them
        $winners = [];
        $losers = [];
        foreach ($response->getData() as $data) {
            if (gettype($data['winner']['bales']) === 'integer') {
                $data['winner']['bales'] = floatval($data['winner']['bales']);
            }
            if (gettype($data['loser']['bales']) === 'integer') {
                $data['loser']['bales'] = floatval($data['loser']['bales']);
            }
            $winners[] = $data['winner'];
            $losers[] = $data['loser'];
        }
        $this->checkStatisticsData($winners, $testKeysID, ['strategy' => 'string', 'bales' => 'double']);
        $this->checkStatisticsData($losers, $testKeysID, ['strategy' => 'string', 'bales' => 'double']);
    }

    private function checkStatisticsByRoundsCount(string $testKeysID, array $datesPeriod = [])
    {
        $response = $this->request('total_statistics_by_rounds_count', $datesPeriod);
        $this->checkStatisticsResponse($response, $testKeysID, [
            'bales' => ['double', 'integer'],
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
        ]);
    }
}