<?php

namespace App\Repository\Service;

use App\Entity\Game;
use App\Entity\GameResult;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;

class TotalStatisticsRepository extends AbstractServiceRepository
{
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
            SUM((SELECT SUM(gr.result) FROM game_result gr WHERE gr.game_id = g.id))/SUM(g.rounds) AS bales,
            COUNT(*) AS gamesCount,
            SUM(g.rounds) AS roundsCount,
            DATE_FORMAT(g.created_at, '%Y-%m-%d') AS gameDate
        FROM game g
        WHERE
        g.user_id = :user_id
        AND g.created_at > :from_date
        AND g.created_at < :to_date
        GROUP BY gameDate
        ORDER BY gameDate ASC
     *
     * @param User $user
     *
     * @return array
     */
    public function getStatisticsByDates(User $user)
    {
        $query = $this->createGameQueryBuilder($user)
            ->select([
                sprintf('SUM_QUERY(%s)/SUM(g.rounds) AS bales', $this->createGameResultBalesSubQuery()->getQuery()->getDQL()),
                'COUNT(g) AS gamesCount',
                'SUM(g.rounds) AS roundsCount',
                sprintf('DATE_FORMAT(g.createdAt, \'%s\') AS gameDate', $this->getParam('database_date_format')),
            ])
            ->groupBy('gameDate')
        ;

        return $query->getQuery()->getArrayResult();
    }

    /**
     * Get dependency of average bales and games count from strategy
     *
     * Request:
        SELECT
            s.name AS strategy,
            SUM(gr.result)/SUM(g.rounds) AS bales,
            COUNT(gr.game_id) AS gamesCount,
            SUM(g.rounds) AS roundsCount
        FROM game_result gr
        INNER JOIN game g ON g.id = gr.game_id
        INNER JOIN strategy s ON s.id = gr.strategy_id
        WHERE g.user_id = :user_id
        AND g.created_at > :from_date
        AND g.created_at < :to_date
        GROUP BY strategy
        ORDER BY bales DESC
     *
     * @param User $user
     *
     * @return array
     */
    public function getStatisticsByStrategies(User $user)
    {
        $query = $this->createQueryBuilder('gr', GameResult::class)
            ->select([
                's.name AS strategy',
                'SUM(gr.result)/SUM(g.rounds) AS bales',
                'COUNT(gr.game) AS gamesCount',
                'SUM(g.rounds) AS roundsCount',
            ])
            ->innerJoin('gr.strategy', 's')
            ->innerJoin('gr.game', 'g')
            ->where('g.user = :user')
            ->setParameter('user', $user)
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
            SUM((SELECT SUM(gr1.result) FROM game_result gr1 WHERE gr1.game_id = g.id))/g.rounds AS bales,
            SUM((SELECT SUM(gr2.result) FROM game_result gr2 WHERE gr2.game_id = g.id)) AS totalBales,
            g.rounds AS roundsCount,
            (SELECT MAX(gr3.result) FROM game_result gr3 WHERE gr3.game_id = g.id) AS bestResultBales,
            (SELECT s4.name FROM game_result gr4 INNER JOIN strategy s4 ON s4.id = gr4.strategy_id WHERE gr4.game_id = g.id AND gr4.result = bestResultBales LIMIT 1) AS bestResultStrategy,
            (SELECT MIN(gr5.result) FROM game_result gr4 WHERE gr4.game_id = g.id) AS worseResultBales,
            (SELECT s6.name FROM game_result gr6 INNER JOIN strategy s6 ON s6.id = gr6.strategy_id WHERE gr6.game_id = g.id AND gr6.result = worseResultBales LIMIT 1) AS worseResultStrategy
        FROM game g
        WHERE g.user_id = :user_id
        AND g.created_at > :from_date
        AND g.created_at < :to_date
        GROUP BY game
        ORDER BY gameDate ASC
     *
     * @param User $user
     *
     * @return array
     */
    public function getStatisticsByGames(User $user)
    {
        $bestResultBalesQueryBuilder = $this->createQueryBuilder('gr3', GameResult::class)
            ->select('MAX(gr3.result)')
            ->andWhere('gr3.game = g.id')
        ;
        $bestResultStrategyQueryBuilder = $this->createQueryBuilder('gr4', GameResult::class)
            ->select('s4.name')
            ->innerJoin('gr4.strategy', 's4')
            ->andWhere('gr4.game = g.id')
            ->andWhere('gr4.result = bestResultBales')
        ;
        $worseResultBalesQueryBuilder = $this->createQueryBuilder('gr5', GameResult::class)
            ->select('MIN(gr5.result)')
            ->andWhere('gr5.game = g.id')
        ;
        $worseResultStrategyQueryBuilder = $this->createQueryBuilder('gr6', GameResult::class)
            ->select('s6.name')
            ->innerJoin('gr6.strategy', 's6')
            ->andWhere('gr6.game = g.id')
            ->andWhere('gr6.result = worseResultBales')
        ;

        $query = $this->createGameQueryBuilder($user)
            ->select([
                'g.name AS game',
                sprintf('DATE_FORMAT(g.createdAt, \'%s\') AS gameDate', $this->getParam('database_date_format')),
                sprintf('SUM_QUERY(%s)/g.rounds AS bales', $this->createGameResultBalesSubQuery('gr1')->getQuery()->getDQL()),
                sprintf('SUM_QUERY(%s) AS totalBales', $this->createGameResultBalesSubQuery('gr2')->getQuery()->getDQL()),
                'g.rounds AS roundsCount',
                sprintf('FIRST(%s) AS bestResultBales', $bestResultBalesQueryBuilder->getDQL()),
                sprintf('FIRST(%s) AS bestResultStrategy', $bestResultStrategyQueryBuilder->getDQL()),
                sprintf('FIRST(%s) AS worseResultBales', $worseResultBalesQueryBuilder->getDQL()),
                sprintf('FIRST(%s) AS worseResultStrategy', $worseResultStrategyQueryBuilder->getDQL()),
            ])
            ->groupBy('game')
            ->orderBy('gameDate', 'ASC')
        ;

        //dd($query->getQuery()->getSQL());

        return $query->getQuery()->getArrayResult();
    }

    /**
     * Get dependency of average bales and games count from rounds count
     *
     * Request:
        SELECT
            SUM((SELECT SUM(gr.result) FROM game_result gr WHERE gr.game_id = g.id))/SUM(g.rounds) AS bales,
            COUNT(g.id) AS gamesCount,
            g.rounds AS roundsCount
        FROM game g
        WHERE g.user_id = :user_id
        AND g.created_at > :from_date
        AND g.created_at < :to_date
        GROUP BY roundsCount
        ORDER BY roundsCount ASC
     *
     * @param User $user
     *
     * @return array
     */
    public function getStatisticsByRoundsCount(User $user)
    {
        $query = $this->createGameQueryBuilder($user)
            ->select([
                sprintf('SUM_QUERY(%s)/SUM(g.rounds) AS bales', $this->createGameResultBalesSubQuery()->getQuery()->getDQL()),
                'COUNT(g) AS gamesCount',
                'g.rounds AS roundsCount',
            ])
            ->groupBy('roundsCount')
            ->orderBy('roundsCount', 'ASC')
        ;

        return $query->getQuery()->getArrayResult();
    }


    private function createGameQueryBuilder(User $user)
    {
        return $this->createQueryBuilder('g', Game::class)
            ->andWhere('g.user = :user')
            ->setParameter('user', $user);
    }

    private function createGameResultBalesSubQuery($alias = 'gr', $gameAlias = 'g')
    {
        return $this->createQueryBuilder($alias, GameResult::class)
            ->select(sprintf('SUM(%s.result)', $alias))
            ->andWhere(sprintf('%s.game = %s.id', $alias, $gameAlias))
        ;
    }
}