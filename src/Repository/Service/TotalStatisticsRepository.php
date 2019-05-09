<?php

namespace App\Repository\Service;

use App\Entity\Game;
use App\Entity\GameResult;
use App\Entity\User;

class TotalStatisticsRepository extends AbstractServiceRepository
{
    private $filtersMap = [
        'fromDate' => 'g.createdAt > :from_date',
        'toDate' => 'g.createdAt < :to_date'
    ];

    public function getFirstAndLastGamesDates(User $user)
    {
        $query = $this->createQueryBuilder('g', Game::class)
            ->select([
                'MIN(g.createdAt) AS start',
                'MAX(g.createdAt) AS end',
            ])
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getSingleResult();
    }

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
    public function getStatisticsByDates(User $user, array $filters)
    {
        $query = $this->createGameResultsJoinedGameQueryBuilder($user, $filters)
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
    public function getStatisticsByStrategies(User $user, array $filters)
    {
        $query = $this->createGameResultsJoinedGameQueryBuilder($user, $filters)
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
     * Get dependency of bales and games count from date
     *
     * Request:
        SELECT
            g.name AS game,
            DATE_FORMAT(g.created_at, '%Y-%m-%d') AS gameDate,
            SUM(gr.result) AS totalBales,
            g.rounds AS roundsCount,
            (SELECT MAX(gr1.result) FROM game_result gr1 WHERE gr1.game_id = gr.game_id) AS bestResultBales,
            (SELECT s2.name FROM game_result gr2 INNER JOIN strategy s2 ON s2.id = gr2.strategy_id WHERE gr2.game_id = gr.game_id AND gr2.result = bestResultBales LIMIT 1) AS bestResultStrategy,
            (SELECT MIN(gr3.result) FROM game_result gr3 WHERE gr3.game_id = gr.game_id) AS worseResultBales,
            (SELECT s4.name FROM game_result gr4 INNER JOIN strategy s4 ON s4.id = gr4.strategy_id WHERE gr4.game_id = gr.game_id AND gr4.result = worseResultBales LIMIT 1) AS worseResultStrategy
        FROM game_result gr
        INNER JOIN game g ON g.id = gr.game_id
        INNER JOIN strategy s ON s.id = gr.strategy_id
        WHERE g.user_id = :user_id
        GROUP BY game, gameDate
        ORDER BY gameDate ASC
     *
     * @param User $user
     *
     * @return array
     */
    public function getStatisticsByGames(User $user, array $filters)
    {
        $bestResultBalesQueryBuilder = $this->createQueryBuilder('gr1', GameResult::class)
            ->select('MAX(gr1.result)')
            ->andWhere('gr1.game = gr.game')
        ;
        $bestResultStrategyQueryBuilder = $this->createQueryBuilder('gr2', GameResult::class)
            ->select('s2.name')
            ->innerJoin('gr2.strategy', 's2')
            ->andWhere('gr2.game = gr.game')
            ->andWhere('gr2.result = bestResultBales')
        ;
        $worseResultBalesQueryBuilder = $this->createQueryBuilder('gr3', GameResult::class)
            ->select('MIN(gr3.result)')
            ->andWhere('gr3.game = gr.game')
        ;
        $worseResultStrategyQueryBuilder = $this->createQueryBuilder('gr4', GameResult::class)
            ->select('s4.name')
            ->innerJoin('gr4.strategy', 's4')
            ->andWhere('gr4.game = gr.game')
            ->andWhere('gr4.result = worseResultBales')
        ;

        $query = $this->createGameResultsJoinedGameQueryBuilder($user, $filters)
            ->select([
                'g.name AS game',
                sprintf('DATE_FORMAT(g.createdAt, \'%s\') AS gameDate', $this->getParam('database_date_format')),
                'SUM(gr.result) AS totalBales',
                'g.rounds AS roundsCount',
                sprintf('FIRST(%s) AS bestResultBales', $bestResultBalesQueryBuilder->getDQL()),
                sprintf('FIRST(%s) AS bestResultStrategy', $bestResultStrategyQueryBuilder->getDQL()),
                sprintf('FIRST(%s) AS worseResultBales', $worseResultBalesQueryBuilder->getDQL()),
                sprintf('FIRST(%s) AS worseResultStrategy', $worseResultStrategyQueryBuilder->getDQL()),
            ])
            ->groupBy('game')
            ->addGroupBy('gameDate')
            ->orderBy('gameDate', 'ASC')
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
    public function getStatisticsByRoundsCount(User $user, array $filters)
    {
        $query = $this->createGameResultsJoinedGameQueryBuilder($user, $filters)
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


    private function createGameResultsJoinedGameQueryBuilder(User $user, array $filters)
    {
        $query = $this->createQueryBuilder('gr', GameResult::class)
            ->innerJoin('gr.game', 'g')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
        ;

        foreach ($filters as $name => $value) {
            if (!isset($this->filtersMap[$name]) || $value === null) {
                continue;
            }
            $filter = $this->filtersMap[$name];
            if (!preg_match("/^.+:(\w+)$/", $filter, $matches) || count($matches) !== 2) {
                continue;
            }
            $param = $matches[1];
            if (strpos($param, '_date') !== false) {
                $value = new \DateTime($value);
                if ($param === 'to_date') {
                    $value->modify('1 day');
                }
            }
            $query->andWhere($filter)->setParameter($param,$value);
        }

        return $query;
    }
}