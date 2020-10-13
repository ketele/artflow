<?php

namespace App\Repository;

use App\Entity\DoodleStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DoodleStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method DoodleStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method DoodleStatus[]    findAll()
 * @method DoodleStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DoodleStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DoodleStatus::class);
    }

    public function findOneByIdField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return DoodleStatus[] Returns an array of DoodleStatus objects
    //  */
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
    public function findOneBySomeField($value): ?DoodleStatus
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
