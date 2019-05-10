<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 25.10.2018
 * Time: 14:58
 */

namespace App\Tests\Web;

use App\Tests\AbstractApiTestCase;

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
}