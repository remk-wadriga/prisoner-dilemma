<?php

namespace App\Doctrine\Filters;

use App\Entity\Game;

class GameBalesForWinFilter extends FilterAbstract
{
    protected $paramName = 'game_balesForWin';

    protected $filterPattern = '%s.balesForWin = :bales_for_win';

    protected $allowedEntities = [Game::class];
}