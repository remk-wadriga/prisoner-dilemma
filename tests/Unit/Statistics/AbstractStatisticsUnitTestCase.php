<?php

namespace App\Tests\Unit\Statistics;

use App\Service\FormatterService;
use App\Tests\AbstractUnitTestCase;
use Doctrine\ORM\QueryBuilder;

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

    protected function addFiltersToQuery(QueryBuilder $queryBuilder, array $filters, string $alias = 'g')
    {
        foreach ($filters as $param => $value) {
            if (strpos($param, 'Date') !== false) {
                $value = new \DateTime($value);
                if ($param == 'toDate') {
                    $value->modify('1 day');
                    $queryBuilder
                        ->andWhere($alias . '.createdAt < :to_date')
                        ->setParameter('to_date', $value->format($this->getParam('backend_date_format')));
                } else {
                    $queryBuilder
                        ->andWhere($alias . '.createdAt > :from_date')
                        ->setParameter('from_date', $value->format($this->getParam('backend_date_format')));
                }
            } else {
                if ($param == 'roundsCount') {
                    $param = 'rounds';
                }
                $queryBuilder
                    ->andWhere(sprintf('%s.%s = :%s', $alias, $param, $param))
                    ->setParameter($param, $value);
            }
        }
    }
}