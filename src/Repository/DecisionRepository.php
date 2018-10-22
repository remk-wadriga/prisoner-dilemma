<?php

namespace App\Repository;

use App\Entity\Decision;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Decision|null find($id, $lockMode = null, $lockVersion = null)
 * @method Decision|null findOneBy(array $criteria, array $orderBy = null)
 * @method Decision[]    findAll()
 * @method Decision[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DecisionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Decision::class);
    }

    /**
     * @param $strategyID
     * @return Decision|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findRootByStrategyId($strategyID)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.strategy = :strategy_id')
            ->andWhere('d.parent is NULL')
            ->setParameter('strategy_id', $strategyID)
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param $strategyID
     * @return Decision[]
     */
    public function findDecisionsByStrategyIdOrderedByIdDesc($strategyID)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.strategy = :strategy_id')
            ->setParameter('strategy_id', $strategyID)
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Decision[] Returns an array of Decision objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Decision
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
