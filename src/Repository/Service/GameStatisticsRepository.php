<?php


namespace App\Repository\Service;


use App\Entity\Game;

class GameStatisticsRepository extends AbstractServiceRepository
{
    public function getStatisticsByStrategies(Game $game)
    {
        dd($game);
    }
}