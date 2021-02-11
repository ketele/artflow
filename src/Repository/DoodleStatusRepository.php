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

    public function findOne($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
