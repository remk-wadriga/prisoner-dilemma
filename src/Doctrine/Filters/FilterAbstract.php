<?php

namespace App\Doctrine\Filters;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class FilterAbstract extends SQLFilter
{
    protected $paramName;

    protected $filterPattern;

    protected $allowedEntities = [];

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (!in_array($targetEntity->getName(), $this->allowedEntities) || !$this->hasParameter($this->paramName)) {
            return '';
        }
        if (!preg_match("/^%s\.(\w+) (.+) :(\w+)$/", $this->filterPattern, $matches) || count($matches) !== 4) {
            return '';
        }
        $field = $matches[1];
        if (!$targetEntity->hasField($field)) {
            return '';
        }

        $value = $this->getParameter($this->paramName);
        $param = $matches[3];
        if (is_string($value)) {
            $value = str_replace('\'', '', $value);
        }
        if (strpos($param, '_date') !== false) {
            $value = new \DateTime($value);
            if ($param === 'to_date') {
                $value->modify('1 day');
            }
            // backend_date_format: 'Y-m-d'
            $value = $value->format('Y-m-d');
        }

        $filter = sprintf($this->filterPattern, $targetTableAlias);

        if (is_string($value)) {
            $value = sprintf('\'%s\'', $value);
        }
        $filter = str_replace('.' . $field, '.' . $targetEntity->getFieldMapping($field)['columnName'], $filter);
        return str_replace(':' . $param, $value, $filter);
    }
}