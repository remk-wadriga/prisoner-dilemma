<?php

namespace App\Doctrine\Filters;

use App\Entity\Game;

class GameBalesForDrawFilter extends FilterAbstract
{
    protected $paramName = 'game_balesForDraw';

    protected $filterPattern = '%s.balesForDraw = :bales_for_draw';

    protected $allowedEntities = [Game::class];
}