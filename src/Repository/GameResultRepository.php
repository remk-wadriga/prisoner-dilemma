<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\GameResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GameResult|null find($id, $lockMode = null, $lockVersion = null)
 * @method GameResult|null findOneBy(array $criteria, array $orderBy = null)
 * @method GameResult[]    findAll()
 * @method GameResult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameResultRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GameResult::class);
    }

    public function findGameBestResult(Game $game)
    {
        $maxResult = $this->createGameResultQueryBuilder($game)
            ->select('MAX(gr.result)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $this->findGameResultStrategyByResult($game, intval($maxResult));
    }

    public function findGameWorseResult(Game $game)
    {
        $minResult = $this->createGameResultQueryBuilder($game)
            ->select('MIN(gr.result)')
            ->getQuery()
            ->getSingleScalarResult();
        return $this->findGameResultStrategyByResult($game, intval($minResult));
    }


    private function findGameResultStrategyByResult(Game $game, int $result): array
    {
        $query = $this->createQueryBuilder('gr')
            ->select([
                's.name AS strategy',
                'gr.result AS bales',
            ])
            ->innerJoin('gr.strategy', 's')
            ->andWhere('gr.game = :game')
            ->andWhere('gr.result = :result')
            ->setParameters(['game' => $game, 'result' => $result])
            ->setMaxResults(1)
        ;

        return $query->getQuery()->getSingleResult();
    }

    private function createGameResultQueryBuilder(Game $game)
    {
        return $this->createQueryBuilder('gr')
            ->andWhere('gr.game = :game')
            ->setParameter('game', $game)
        ;
    }
}
