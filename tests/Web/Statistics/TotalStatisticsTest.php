<?php

namespace App\Tests\Web\Statistics;

class TotalStatisticsTest extends AbstractStatisticsApiTestCase
{
    public function testStatisticsByDates()
    {
        // 1. Login as user
        $this->logInAsUser();

        // 2. Get total statistics without dates range, check it and remember request params to compare them with filtered param response
        $oldData = $this->checkStatisticsByDates('total_statics_by_dates');

        // 3. Get total statistics with dates range and check it
        $this->checkStatisticsByDates('total_statics_by_dates_with_dates', $this->getRandomDatesPeriod());

        // 4. Get total statistics filtered by games params and compare them with not filtered response
        $testKeysID = 'total_statics_by_dates_with_game_params';
        $filters = $this->createRandomGameParamsFilters();
        $data = $this->checkStatisticsByDates($testKeysID, $filters);
        $this->assertNotEquals($data, $oldData, sprintf('Test %s failed. There is no difference between responses with filters and without them. User: #%s, filterParams: %s',
            $testKeysID, $this->user->getId(), json_encode($filters)));
    }

    public function testStatisticsByStrategies()
    {
        // 1. Login as user
        $this->logInAsUser();

        // 2. Get total statistics without dates range, check it and remember request params to compare them with filtered param response
        $oldData = $this->checkStatisticsByStrategies('total_statics_by_strategies');

        // 3. Get total statistics with dates range and check it
        $this->checkStatisticsByStrategies('total_statics_by_strategies_with_dates', $this->getRandomDatesPeriod());

        // 4. Get total statistics filtered by games params and compare them with not filtered response
        $testKeysID = 'total_statics_by_strategies_with_game_params';
        $filters = $this->createRandomGameParamsFilters();
        $data = $this->checkStatisticsByStrategies($testKeysID, $filters);
        $this->assertNotEquals($data, $oldData, sprintf('Test %s failed. There is no difference between responses with filters and without them. User: #%s, filterParams: %s',
            $testKeysID, $this->user->getId(), json_encode($filters)));
    }

    public function testStatisticsByGames()
    {
        // 1. Login as user
        $this->logInAsUser();

        // 2. Get total statistics without dates range, check it and remember request params to compare them with filtered param response
        $oldData = $this->checkStatisticsByGames('total_statics_by_games');

        // 3. Get total statistics with dates range and check it
        $this->checkStatisticsByGames('total_statics_by_games_with_dates', $this->getRandomDatesPeriod());

        // 4. Get total statistics filtered by games params and compare them with not filtered response
        $testKeysID = 'total_statics_by_games_with_game_params';
        $filters = $this->createRandomGameParamsFilters();
        $data = $this->checkStatisticsByGames($testKeysID, $filters);
        $this->assertNotEquals($data, $oldData, sprintf('Test %s failed. There is no difference between responses with filters and without them. User: #%s, filterParams: %s',
            $testKeysID, $this->user->getId(), json_encode($filters)));
    }

    public function testStatisticsByRoundsCount()
    {
        // 1. Login as user
        $this->logInAsUser();

        // 2. Get total statistics without dates range, check it and remember request params to compare them with filtered param response
        $oldData = $this->checkStatisticsByRoundsCount('total_statics_by_rounds_count');

        // 3. Get total statistics with dates range and check it
        $this->checkStatisticsByRoundsCount('total_statics_by_rounds_count_with_dates', $this->getRandomDatesPeriod());

        // 4. Get total statistics filtered by games params and compare them with not filtered response
        $testKeysID = 'total_statics_by_rounds_count_with_game_params';
        $filters = $this->createRandomGameParamsFilters();
        $data = $this->checkStatisticsByRoundsCount($testKeysID, $filters);
        $this->assertNotEquals($data, $oldData, sprintf('Test %s failed. There is no difference between responses with filters and without them. User: #%s, filterParams: %s',
            $testKeysID, $this->user->getId(), json_encode($filters)));
    }


    private function checkStatisticsByDates(string $testKeysID, array $requestParams = [])
    {
        $response = $this->request(['total_statistics_by_dates', $requestParams]);
        $this->checkResponseParams($response, $testKeysID, [
            'bales' => 'double',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
            'gameDate' => 'string',
        ]);
        return $response->getData();
    }

    private function checkStatisticsByStrategies(string $testKeysID, array $requestParams = [])
    {
        $response = $this->request(['total_statistics_by_strategies', $requestParams]);
        $this->checkResponseParams($response, $testKeysID, [
            'id' => 'integer',
            'strategy' => 'string',
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
            'bales' => ['double', 'integer'],
        ]);
        return $response->getData();
    }

    private function checkStatisticsByGames(string $testKeysID, array $requestParams = [])
    {
        // 1. Get total statistics and check it
        $response = $this->request(['total_statistics_by_games', $requestParams]);
        $this->checkResponseParams($response, $testKeysID, [
            'id' => 'integer',
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

        return $response->getData();
    }

    private function checkStatisticsByRoundsCount(string $testKeysID, array $requestParams = [])
    {
        $response = $this->request(['total_statistics_by_rounds_count', $requestParams]);
        $this->checkResponseParams($response, $testKeysID, [
            'bales' => ['double', 'integer'],
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
        ]);
        return $response->getData();
    }
}