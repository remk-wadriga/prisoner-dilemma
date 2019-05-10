<?php

namespace App\Doctrine\Filters;

use App\Entity\Game;

class ToDateFilter extends FilterAbstract
{
    protected $paramName = 'toDate';

    protected $filterPattern = '%s.createdAt < :to_date';

    protected $allowedEntities = [Game::class];
}