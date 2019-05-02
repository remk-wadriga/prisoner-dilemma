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

    /**
     * Get strategy dependency of average bales from game rounds
     *
     * Request:
     *   SELECT
     *       SUM(gr.result) / COUNT(gr.strategy_id) AS bales,
     *       COUNT(gr.id) AS gamesCount,
     *       g.rounds AS roundsCount
     *   FROM `game_result` gr
     *   INNER JOIN game g ON g.id = gr.game_id
     *   WHERE gr.strategy_id = 6
     *   GROUP BY roundsCount
     *   ORDER BY roundsCount ASC
     *
     * @param Strategy $strategy
     *
     * @return array
     */
    public function getStatisticsByRoundsCount(Strategy $strategy)
    {
        $query = $this->createQueryBuilder('gr', GameResult::class)
            ->select([
                'SUM(gr.result) / COUNT(gr.strategy) AS bales',
                'COUNT(gr) AS gamesCount',
                'g.rounds AS roundsCount',
            ])
            ->innerJoin('gr.game', 'g')
            ->andWhere('gr.strategy = :strategy')
            ->setParameter('strategy', $strategy)
            ->orderBy('roundsCount', 'ASC')
            ->groupBy('roundsCount')
        ;

        return $query->getQuery()->getArrayResult();
    }
}