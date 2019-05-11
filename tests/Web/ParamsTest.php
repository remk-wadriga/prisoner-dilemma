<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 25.10.2018
 * Time: 14:58
 */

namespace App\Tests\Web;

use App\Tests\AbstractApiTestCase;
use App\Tests\ApiResponse;
use Faker\Factory;

class ParamsTest extends AbstractApiTestCase
{
    public function testGameParams()
    {
        // 1. Login as default user
        $this->logInAsUser();

        // 2. Make request
        $response = $this->request('params_game');

        // 3. Check response params
        $this->checkResponseParams($response, 'game_params', [
            'rounds' => 'integer',
            'balesForWin' => 'integer',
            'balesForLoos' => 'integer',
            'balesForCooperation' => 'integer',
            'balesForDraw' => 'integer',
        ], true);
    }

    public function testStrategyParams()
    {
        // 1. Login as default user
        $this->logInAsUser();

        // 2. Make request
        $response = $this->request('params_strategy');

        // 3. Check response params
        $this->checkResponseParams($response, 'strategy_params', [
            'maxRandomDecisionsCount' => 'integer',
            'chanceOfExtendingBranch' => 'integer',
            'randomDecisionChance' => 'integer',
            'copyDecisionChance' => 'integer',
            'acceptDecisionChance' => 'integer',
        ], true);
    }

    public function testStatisticsDatesParams()
    {
        // 1. Login as default user
        $this->logInAsUser();

        // 2. Make request
        $response = $this->request('params_statistics_dates');

        // 3. Check response params
        $this->checkResponseParams($response, 'params_statistics_dates', [
            'start' => 'string',
            'end' => 'string',
        ], true);
    }

    public function testGameParamsFilters()
    {
        // 1. Login as default user
        $this->logInAsUser();

        // 2. Make request without filtering and check it
        $response = $this->request('params_game_filters');
        $this->checkGameParamsResponse($response, 'params_statistics_dates');

        // 3. Remember params without filtering
        $oldParams = $response->getData();

        // 4. Make request filtered by dates and check the difference between old and new params
        $testKeysID = 'params_statistics_dates_with_dates_filters';
        $filters = $this->getRandomDatesPeriod(1);
        $response = $this->request(['params_game_filters', $filters]);
        $this->checkGameParamsResponse($response, $testKeysID);
        $newParams = $response->getData();
        $this->assertNotEquals($oldParams, $newParams, sprintf('Test %s failed. There is no difference between responses with filters and without them. User: #%s, filterParams: %s',
            $testKeysID, $this->user->getId(), json_encode($filters)));

        // 4. Make request filtered by games params and check the difference between old and new params
        $testKeysID = 'params_statistics_dates_with_game_params_filters';
        $faker = Factory::create();
        $filters = [];
        foreach ($oldParams as $filter => $values) {
            if (empty($values)) {
                continue;
            }
            $filters['game_' . $filter] = $faker->randomElement($values);
        }
        $response = $this->request(['params_game_filters', $filters]);
        $this->checkGameParamsResponse($response, $testKeysID);
        $newParams = $response->getData();
        $this->assertNotEquals($oldParams, $newParams, sprintf('Test %s failed. There is no difference between responses with filters and without them. User: #%s, filterParams: %s',
            $testKeysID, $this->user->getId(), json_encode($filters)));
    }


    private function checkGameParamsResponse(ApiResponse $response, string $testKeysID)
    {
        $this->checkResponseParams($response, $testKeysID, [
            'roundsCount' => 'array',
            'balesForWin' => 'array',
            'balesForLoos' => 'array',
            'balesForCooperation' => 'array',
            'balesForDraw' => 'array',
        ], true);
    }
}