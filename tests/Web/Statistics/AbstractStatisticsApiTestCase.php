<?php

namespace App\Tests\Web\Statistics;

use App\Tests\AbstractApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\ApiResponse;

class AbstractStatisticsApiTestCase extends AbstractApiTestCase
{
    protected function checkStatisticsResponse(ApiResponse $response, string $testKeysID, array $params)
    {
        // Check status
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Wrong test "%s" response format, status code must be equal to %s, but it is not. It is: %s. The content is: %s',
                $testKeysID, Response::HTTP_FORBIDDEN, $response->getStatus(), $response->getContent()));
        // Check data
        $data = $response->getData();
        $this->assertInternalType('array', $data, sprintf('Wrong test "%s" response. The response data must be an array, but "%s" given. Data: %s',
            $testKeysID, gettype($data), $response->getContent()));
        foreach ($data as $stats) {
            $jsonData = json_encode($stats);
            $this->assertInternalType('array', $stats, sprintf('Wrong test "%s" response. Each response data item must be an array, but "%s" given. Data: %s',
                $testKeysID, gettype($stats), $jsonData));

            foreach ($params as $attr => $type) {
                if (is_array($type)) {
                    $type1 = $type[0];
                    $type2 = $type[1];
                    $type = $type1;
                    if (isset($stats[$attr]) && gettype($stats[$attr]) === $type2) {
                        settype($stats[$attr], $type1);
                    }
                }

                $this->assertArrayHasKey($attr, $stats, sprintf('Wrong test "%s" response. Each response data item have the "%s" param, but it\'s not. Data: %s',
                    $testKeysID, $attr, $jsonData));
                $this->assertInternalType($type, $stats[$attr], sprintf('Wrong test "%s" response. Each response data.%s item must be a %s, but "%s" given. Data: %s',
                    $testKeysID, $attr, $type, gettype($stats[$attr]), $jsonData));
            }
        }
    }

    protected function checkStatisticsData($statistics, string $testKeysID, array $params)
    {
        $this->assertInternalType('array', $statistics, sprintf('Test case "%s" failed. The statistics data must be an array, but "%s" given. Data: %s',
            $testKeysID, gettype($statistics), json_encode($statistics)));

        foreach ($statistics as $stats) {
            $jsonData = json_encode($stats);
            $this->assertInternalType('array', $stats, sprintf('Test case "%s" failed. Each statistics data item must be an array, but "%s" given. Data: %s',
                $testKeysID, gettype($stats), $jsonData));

            foreach ($params as $attr => $type) {
                $this->assertArrayHasKey($attr, $stats, sprintf('Test case "%s" failed. Each statistics data item have the "%s" param, but it\'s not. Data: %s',
                    $testKeysID, $attr, $jsonData));
                $this->assertInternalType($type, $stats[$attr], sprintf('Test case "%s" failed. Each statistics data.%s item must be a %s, but "%s" given. Data: %s',
                    $testKeysID, $attr, $type, gettype($stats[$attr]), $jsonData));
            }
        }
    }
}