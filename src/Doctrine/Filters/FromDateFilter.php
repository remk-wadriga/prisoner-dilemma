<?php

namespace App\Doctrine\Filters;

use App\Entity\Game;

class FromDateFilter extends FilterAbstract
{
    protected $paramName = 'fromDate';

    protected $filterPattern = '%s.createdAt > :from_date';

    protected $allowedEntities = [Game::class];
}