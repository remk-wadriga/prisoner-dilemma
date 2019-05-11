<?php

namespace App\Doctrine\Filters;

use App\Entity\Game;

class GameBalesForLoosFilter extends FilterAbstract
{
    protected $paramName = 'game_balesForLoos';

    protected $filterPattern = '%s.balesForLoos = :bales_for_loos';

    protected $allowedEntities = [Game::class];
}