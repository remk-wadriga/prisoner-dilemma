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

    public function testStatisticsByRoundsCount()
    {
        $testKeysID = 'total_statics_by_rounds_count';

        // 1. Login as user
        $this->logInAsUser();

        // 2. Get total statistics and check it
        $response = $this->request('total_statistics_by_rounds_count');
        $this->checkStatisticsResponse($response, $testKeysID, [
            'bales' => 'double',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
        ]);
    }
}