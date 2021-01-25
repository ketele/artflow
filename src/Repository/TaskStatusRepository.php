<?php

namespace App\Repository;

use App\Entity\TaskStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TaskStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskStatus[]    findAll()
 * @method TaskStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskStatus::class);
    }

    public function findOne($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function getUserStatuses($user)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->orWhere('t.user IS NULL')
            ->setParameter('user', $user)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param bool $params
     * @return mixed
     */
    public function getStatuses($params = false){
        $queryBuilder = $this->createQueryBuilder('d');

        $opt = [
            'select' => 'd',
            'where' => null,
            'parameters' => [],
            'order' => null,
            'maxResults' => 3,
        ];

        if (!empty($params))
            $opt = array_merge($opt,$params);

        extract($opt);

        $queryBuilder->select($select);

        if( !empty($where) )
            foreach( $where AS $w )
                $queryBuilder->andWhere($w);

        if( !empty($parameters) )
            foreach( $parameters AS $p_key => $p )
                $queryBuilder->setParameter($p_key, $p);

        if( !empty($order) ) {
            foreach ($order AS $o)
                $queryBuilder->orderBy($o[0], $o[1]);
        }

        if(is_numeric($maxResults))
            $queryBuilder->setMaxResults($maxResults);

        $query =    $queryBuilder->getQuery();

        return $query->getResult();
    }

    // /**
    //  * @return TaskStatus[] Returns an array of TaskStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TaskStatus
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
