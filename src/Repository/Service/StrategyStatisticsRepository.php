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
    /**
     * Get strategy dependency of average bales and games count from game date
     *
     * Request:
        SELECT
            SUM(gr.result) / SUM(g.rounds) AS bales,
            COUNT(gr.game_id) AS gamesCount,
            DATE_FORMAT(g.created_at, '%Y-%m-%d') AS gameDate
        FROM `game_result` gr
        INNER JOIN game g ON g.id = gr.game_id
        WHERE gr.strategy_id = 6
        GROUP BY gameDate
        ORDER BY gameDate DESC
     *
     * @param Strategy $strategy
     *
     * @return array
     */
    public function getStatisticsByDates(Strategy $strategy)
    {
        $query = $this->createGameResultsJoinedGameQueryBuilder($strategy)
            ->select([
                'SUM(gr.result) / SUM(g.rounds) AS bales',
                'COUNT(gr.game) AS gamesCount',
                sprintf('DATE_FORMAT(g.createdAt, \'%s\') AS gameDate', $this->getParam('database_date_format')),
            ])
            ->groupBy('gameDate')
            ->orderBy('gameDate', 'ASC')
        ;

        return $query->getQuery()->getArrayResult();
    }

    /**
     * Get strategy dependency of average bales from game rounds
     *
     * Request:
        SELECT
            SUM(gr.result) / SUM(g.rounds) AS bales,
            COUNT(gr.game_id) AS gamesCount,
            g.rounds AS roundsCount
        FROM `game_result` gr
        INNER JOIN game g ON g.id = gr.game_id
        WHERE gr.strategy_id = :strategy_id
        GROUP BY roundsCount
        ORDER BY roundsCount ASC
     *
     * @param Strategy $strategy
     *
     * @return array
     */
    public function getStatisticsByRoundsCount(Strategy $strategy)
    {
        $query = $this->createGameResultsJoinedGameQueryBuilder($strategy)
            ->select([
                'SUM(gr.result) / SUM(g.rounds) AS bales',
                'COUNT(gr.game) AS gamesCount',
                'g.rounds AS roundsCount',
            ])
            ->groupBy('roundsCount')
            ->orderBy('roundsCount', 'ASC')
        ;

        return $query->getQuery()->getArrayResult();
    }



    private function createGameResultsJoinedGameQueryBuilder(Strategy $strategy)
    {
        return $this->createQueryBuilder('gr', GameResult::class)
            ->innerJoin('gr.game', 'g')
            ->andWhere('gr.strategy = :strategy')
            ->setParameter('strategy', $strategy)
        ;
    }
}