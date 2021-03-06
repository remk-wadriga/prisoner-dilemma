<?php

namespace App\Repository;

use App\Entity\Strategy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Strategy|null find($id, $lockMode = null, $lockVersion = null)
 * @method Strategy|null findOneBy(array $criteria, array $orderBy = null)
 * @method Strategy[]    findAll()
 * @method Strategy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StrategyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Strategy::class);
    }

    /**
     * @return Strategy[]
     */
    public function findAllOrderedByCreatedAtDesc()
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
