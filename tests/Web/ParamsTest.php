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
use Symfony\Component\HttpFoundation\Response;

class ParamsTest extends AbstractApiTestCase
{
    public function testGameParams()
    {
        // 1. Login as default user
        $this->logInAsUser();

        // 2. Make request
        $response = $this->request('params_game');

        // 3. Check response params
        $params = [
            ['rounds', 'integer'],
            ['balesForWin', 'integer'],
            ['balesForLoos', 'integer'],
            ['balesForCooperation', 'integer'],
            ['balesForDraw', 'integer'],
        ];
        $this->checkIsResponseContains($response, $params, 'Test "Check game params" is failed.');
    }

    public function testStrategyParams()
    {
        // 1. Login as default user
        $this->logInAsUser();

        // 2. Make request
        $response = $this->request('params_strategy');

        // 3. Check response params
        $params = [
            ['maxRandomDecisionsCount', 'integer'],
            ['chanceOfExtendingBranch', 'integer'],
            ['randomDecisionChance', 'integer'],
            ['copyDecisionChance', 'integer'],
            ['acceptDecisionChance', 'integer'],
        ];
        $this->checkIsResponseContains($response, $params, 'Test "Check strategy params" is failed.');
    }

    public function testStatisticsDatesParams()
    {
        // 1. Login as default user
        $this->logInAsUser();

        // 2. Make request
        $response = $this->request('params_statistics_dates');

        // 3. Check response params
        $params = [
            ['start', 'string'],
            ['end', 'string'],
        ];
        $this->checkIsResponseContains($response, $params, 'Test "params_statistics_dates" is failed.');
    }


    private function checkIsResponseContains(ApiResponse $response, array $params, string $message = '')
    {
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('%s expected http response code is %s, %s given', $message, Response::HTTP_OK, $response->getStatus()));

        $data = $response->getData();
        $this->assertInternalType('array', $data,
            sprintf('%s The response body must be an array, but it\'s not. It is %s', $message, $response->getContent()));

        foreach ($params as $key) {
            $type = null;
            if (is_array($key)) {
                $type = $key[1];
                $key = $key[0];
            }
            $this->assertArrayHasKey($key, $data,
                sprintf('%s Response not contains param "%s". Response is: "%s"', $message, $key, $response->getContent()));
            if ($type !== null) {
                $this->assertInternalType($type, $data[$key],
                    sprintf('%s The response param "%s" must have type "%s", but it\'s not. It is %s', $message, $key, $type, $data[$key]));
            }
        }
    }
}