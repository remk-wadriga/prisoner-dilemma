<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 25.04.2019
 * Time: 16:17
 */

namespace App\Repository\Service;

use App\Entity\GameResult;
use App\Entity\Strategy;

class StrategyStatisticsRepository extends AbstractServiceRepository
{
    public function getStrategyGamesResults(Strategy $strategy)
    {
        $gamesResultsQuery = $this->createQueryBuilder('gr', GameResult::class)
            ->select(['gr', 'g'])
            ->innerJoin('gr.game', 'g')
            ->andWhere('gr.strategy = :strategy')
            ->setParameter('strategy', $strategy)
            ->orderBy('gr.result', 'ASC')
        ;

        $results = [];
        foreach ($gamesResultsQuery->getQuery()->getResult() as $gameResult) {
            /** @var GameResult $gameResult */
            $results[] = [
                'game' => $gameResult->getGame()->getName(),
                'date' => $gameResult->getGame()->getCreatedAt()->format($this->getFrontendDateTimeFormat()),
                'result' => $gameResult->getResult(),
            ];
        }

        return $results;
    }
}