<?php

namespace App\Repository;

use App\Entity\Doodle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Doodle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Doodle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Doodle[]    findAll()
 * @method Doodle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DoodleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Doodle::class);
    }

    public function findOne($value): ?Doodle
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return Doodle[] Returns an array of Doodle objects
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
    public function findOneBySomeField($value): ?Doodle
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
