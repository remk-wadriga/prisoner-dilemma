<?php

namespace App\Repository\Service;

use App\Entity\GameResult;
use App\Entity\User;

class TotalStatisticsRepository extends AbstractServiceRepository
{
    /**
     * Get dependency of average bales and games count from game date
     *
     * Request:
        SELECT
            SUM(gr.result) / SUM(g.rounds) AS bales,
            COUNT(gr.game_id) AS gamesCount,
            SUM(g.rounds) AS roundsCount,
            DATE_FORMAT(g.created_at, '%Y-%m-%d') AS gameDate
        FROM game_result gr
        INNER JOIN game g ON g.id = gr.game_id
        WHERE g.user_id = :user_id
        GROUP BY gameDate
        ORDER BY gameDate DESC
     *
     * @param User $user
     *
     * @return array
     */
    public function getStatisticsByDates(User $user)
    {
        $query = $this->createGameResultsJoinedGameQueryBuilder($user)
            ->select([
                'SUM(gr.result) / SUM(g.rounds) AS bales',
                'COUNT(gr.game) AS gamesCount',
                'SUM(g.rounds) AS roundsCount',
                sprintf('DATE_FORMAT(g.createdAt, \'%s\') AS gameDate', $this->getParam('database_date_format')),
            ])
            ->groupBy('gameDate')
            ->orderBy('gameDate', 'ASC')
        ;

        return $query->getQuery()->getArrayResult();
    }

    /**
     * Get dependency of average bales and games count from strategy
     *
     * Request:
        SELECT
            s.name AS strategy,
            COUNT(gr.game_id) AS gamesCount,
            SUM(g.rounds) AS roundsCount,
            SUM(gr.result) / SUM(g.rounds) AS bales
        FROM game_result gr
        INNER JOIN game g ON g.id = gr.game_id
        INNER JOIN strategy s ON s.id = gr.strategy_id
        WHERE g.user_id = :user_id
        GROUP BY strategy
        ORDER BY bales DESC
     *
     * @param User $user
     *
     * @return array
     */
    public function getStatisticsByStrategies(User $user)
    {
        $query = $this->createGameResultsJoinedGameQueryBuilder($user)
            ->select([
                's.name AS strategy',
                'COUNT(gr.game) AS gamesCount',
                'SUM(g.rounds) AS roundsCount',
                'SUM(gr.result) / SUM(g.rounds) AS bales',
            ])
            ->innerJoin('gr.strategy', 's')
            ->groupBy('strategy')
            ->orderBy('bales', 'DESC')
        ;

        return $query->getQuery()->getArrayResult();
    }

    /**
     * Get dependency of average bales and games count from rounds count
     *
     * Request:
        SELECT
            SUM(gr.result) / SUM(g.rounds) AS bales,
            COUNT(gr.game_id) AS gamesCount,
            g.rounds AS roundsCount
        FROM game_result gr
        INNER JOIN game g ON g.id = gr.game_id
        WHERE g.user_id = :user_id
        GROUP BY roundsCount
        ORDER BY roundsCount ASC
     *
     * @param User $user
     *
     * @return array
     */
    public function getStatisticsByRoundsCount(User $user)
    {
        $query = $this->createGameResultsJoinedGameQueryBuilder($user)
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


    private function createGameResultsJoinedGameQueryBuilder(User $user)
    {
        return $this->createQueryBuilder('gr', GameResult::class)
            ->innerJoin('gr.game', 'g')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
        ;
    }
}