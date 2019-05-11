<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\GameResult;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findAllOrderedByCreatedAtDesc(int $userID = null)
    {
        $queryBuilder = $this->createQueryBuilder('g')
            ->addSelect('gr')
            ->leftJoin('g.gameResults', 'gr')
            ->orderBy('g.updatedAt', 'DESC')
        ;

        if ($userID !== null) {
            $queryBuilder
                ->andWhere('g.user = :user_id')
                ->setParameter('user_id', $userID)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function getUserGamesParams(User $user)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select([
                'CONCAT(UNIQUE(g.rounds)) AS roundsCount',
                'CONCAT(UNIQUE(g.balesForWin)) AS balesForWin',
                'CONCAT(UNIQUE(g.balesForLoos)) AS balesForLoos',
                'CONCAT(UNIQUE(g.balesForCooperation)) AS balesForCooperation',
                'CONCAT(UNIQUE(g.balesForDraw)) AS balesForDraw',
            ])
            ->from(GameResult::class, 'gr')
            ->innerJoin('gr.game', 'g')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getSingleResult();
    }
}
