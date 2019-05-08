<?php

namespace App\Tests\Web\Statistics;

class TotalStatisticsTest extends AbstractStatisticsApiTestCase
{
    public function testStatisticsByDates()
    {
        $testKeysID = 'total_statics_by_dates';

        // 1. Login as user
        $this->logInAsUser();

        // 2. Get total statistics and check it
        $response = $this->request('total_statistics_by_dates');
        $this->checkStatisticsResponse($response, $testKeysID, [
            'bales' => 'double',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
            'gameDate' => 'string',
        ]);
    }

    public function testStatisticsByStrategies()
    {
        $testKeysID = 'total_statics_by_strategies';

        // 1. Login as user
        $this->logInAsUser();

        // 2. Get total statistics and check it
        $response = $this->request('total_statistics_by_strategies');
        $this->checkStatisticsResponse($response, $testKeysID, [
            'strategy' => 'string',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
            'bales' => 'double',
        ]);
    }

    public function testStatisticsByGames()
    {
        $testKeysID = 'total_statics_by_games';

        // 1. Login as user
        $this->logInAsUser();

        // 2. Get total statistics and check it
        $response = $this->request('total_statistics_by_games');
        $this->checkStatisticsResponse($response, $testKeysID, [
            'game' => 'string',
            'gameDate' => 'string',
            'totalBales' => 'integer',
            'bales' => 'double',
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

    public function testStatisticsByRoundsCount()
    {
        $testKeysID = 'total_statics_by_rounds_count';

        // 1. Login as user
        $this->logInAsUser();

        // 2. Get total statistics and check it
        $response = $this->request('total_statistics_by_rounds_count');
        $this->checkStatisticsResponse($response, $testKeysID, [
            'bales' => ['double', 'integer'],
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
        ]);
    }
}