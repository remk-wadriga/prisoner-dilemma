<?php

namespace App\Repository;

use App\Entity\IndividualGameResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method IndividualGameResult|null find($id, $lockMode = null, $lockVersion = null)
 * @method IndividualGameResult|null findOneBy(array $criteria, array $orderBy = null)
 * @method IndividualGameResult[]    findAll()
 * @method IndividualGameResult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndividualGameResultRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, IndividualGameResult::class);
    }

//    /**
//     * @return IndividualGameResult[] Returns an array of IndividualGameResult objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IndividualGameResult
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
