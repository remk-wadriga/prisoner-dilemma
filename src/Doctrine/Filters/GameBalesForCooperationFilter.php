<?php

namespace App\Doctrine\Filters;

use App\Entity\Game;

class GameBalesForCooperationFilter extends FilterAbstract
{
    protected $paramName = 'game_balesForCooperation';

    protected $filterPattern = '%s.balesForCooperation = :bales_for_cooperation';

    protected $allowedEntities = [Game::class];
}