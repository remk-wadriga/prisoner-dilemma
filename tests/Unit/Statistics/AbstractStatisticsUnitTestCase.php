<?php

namespace App\Tests\Unit\Statistics;

use App\Service\FormatterService;
use App\Tests\AbstractUnitTestCase;

class AbstractStatisticsUnitTestCase extends AbstractUnitTestCase
{
    protected $formatterService;

    protected function getFormatterService(): FormatterService
    {
        if ($this->formatterService !== null) {
            return $this->formatterService;
        }

        return $this->formatterService = new FormatterService($this->entityManager, self::$kernel->getContainer());
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