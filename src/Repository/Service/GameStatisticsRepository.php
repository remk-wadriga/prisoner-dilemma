<?php


namespace App\Repository\Service;


use App\Entity\Game;
use App\Entity\GameResult;

class GameStatisticsRepository extends AbstractServiceRepository
{
    /**
     * Get game dependency of average bales and strategy in particular game
     *
     * Request:
        SELECT
            s.name AS strategy,
            gr.result AS bales
        FROM game_result gr
        INNER JOIN game g ON g.id = gr.game_id
        INNER JOIN strategy s ON s.id = gr.strategy_id
        WHERE gr.game_id = 173
        ORDER BY bales DESC
     *
     * @param Game $game
     * @return array
     */
    public function getStatisticsByStrategies(Game $game)
    {
        $query = $this->createGameResultsJoinedGameAndStrategyQueryBuilder($game)
            ->select([
                's.name AS strategy',
                'gr.result AS bales'
            ])
            ->orderBy('bales', 'DESC');

        return $query->getQuery()->getArrayResult();
    }

    private function createGameResultsJoinedGameAndStrategyQueryBuilder(Game $game)
    {
        return $this->createQueryBuilder('gr', GameResult::class)
            ->innerJoin('gr.game', 'g')
            ->innerJoin('gr.strategy', 's')
            ->andWhere('gr.game = :game')
            ->setParameter('game', $game)
        ;
    }
}