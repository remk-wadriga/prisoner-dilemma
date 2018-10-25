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

class ParamsTest extends AbstractApiTestCase
{
    public function testGameParams()
    {
        // 1. Login as default user
        $this->logInAsUser();

        // 2. Make request
        $response = $this->request('params_game');

        // 3. Check response params
        $params = ['rounds', 'balesForWin', 'balesForLoos', 'balesForCooperation', 'balesForDraw'];
        $this->checkIsResponseContains($response, $params, 'Test "Check game params" failed.');
    }

    public function testStrategyParams()
    {
        // 1. Login as default user
        $this->logInAsUser();

        // 2. Make request
        $response = $this->request('params_strategy');

        // 3. Check response params
        $params = ['maxRandomDecisionsCount', 'chanceOfExtendingBranch', 'randomDecisionChance', 'copyDecisionChance', 'acceptDecisionChance'];
        $this->checkIsResponseContains($response, $params, 'Test "Check strategy params" failed.');
    }


    private function checkIsResponseContains(ApiResponse $response, array $params, string $message = '')
    {
        foreach ($params as $key) {
            $this->assertArrayHasKey($key, $response->getData(),
                sprintf('%s Response not contains param "%s". Response is: "%s"', $message, $key, $response->getContent()));
        }
    }
}