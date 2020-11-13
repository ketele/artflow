<?php

namespace App\Repository;

use App\Entity\Doodle;
use App\Entity\DoodleStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Doodle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Doodle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Doodle[]    findAll()
 * @method Doodle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DoodleRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct($registry, Doodle::class);
    }

    /**
     * @param $value
     * @return Doodle|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOne($value): ?Doodle
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findByStatus($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.status = :val')
            ->setParameter('val', $value)
            ->orderBy('d.createdAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByStatusTheMostPopular($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.status = :val')
            ->setParameter('val', $value)
            ->orderBy('d.popularity', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getDoodles($params = false){
        $opt = [
            'where' => ['d.status = ' . DoodleStatus::STATUS_PUBLISHED],
            'order' => [['d.popularity', 'DESC']],
            'max_results' => 3,
        ];

        if (!empty($params))
            $opt = array_merge($opt,$params);

        extract($opt);

        $queryBuilder = $this->createQueryBuilder('d');
        if( !empty($where) )
            foreach( $where AS $w )
                $queryBuilder->andWhere($w);

        if( !empty($order) )
            foreach( $order AS $o )
                $queryBuilder->orderBy($o[0], $o[1]);

            $queryBuilder->setMaxResults($max_results);
        $query =    $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function save(Doodle $doodle)
    {
        $metadata = $this->entityManager->getClassMetadata(get_class($doodle));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_AUTO);
        $this->entityManager->persist($doodle);
        $this->entityManager->flush();
    }

    public function repairIpTree(){


        return true;
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
