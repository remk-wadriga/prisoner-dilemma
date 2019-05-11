<?php

namespace App\Doctrine\Filters;

use App\Entity\Game;

class GameRoundsCountFilter extends FilterAbstract
{
    protected $paramName = 'game_roundsCount';

    protected $filterPattern = '%s.rounds = :rounds_count';

    protected $allowedEntities = [Game::class];
}