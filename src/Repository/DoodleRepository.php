<?php

namespace App\Repository;

use App\Entity\Doodle;
use App\Entity\DoodleStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

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

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        $query = $this->createQueryBuilder('d')
            ->andWhere('d.id = :val')
            ->setParameter('val', $value)
            ->getQuery();

        return $query->getOneOrNullResult();
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
        $this->repairIpTree();
    }

    public function repairIpTree()
    {
        $updated_doodles = array();
        $doodle = $this->findWrongIpTreeRow();

        while( !empty( $doodle ) )
        {
            $doodle_id = $doodle->getId();

            if (!in_array($doodle_id, $updated_doodles))
            {
                $updated_doodles[] = $doodle_id;

                $this->_user_ip_tree = array();
                $ip_tree = $this->generateDoodleIpTree($doodle_id);
                $doodle->setIpTree(implode('.', $ip_tree));
                $this->entityManager->persist($doodle);
                $this->entityManager->flush();

                unset($doodle);
                $doodle = $this->findWrongIpTreeRow(array((!empty($updated_doodles)) ? 'child.id NOT IN ( ' . implode(',', $updated_doodles) . ' )' : 'child.id = child.id'));
            }
        }

        return true;
    }

    public function findWrongIpTreeRow( $where = array() )
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        if( !empty($where) )
            foreach( $where AS $w )
                $queryBuilder->andWhere($w);

        $query = $queryBuilder->select('child')
            ->from(Doodle::class,'child')
            ->leftJoin(Doodle::class,'parent',\Doctrine\ORM\Query\Expr\Join::WITH, 'parent.id = child.sourceDoodleId')
            ->andWhere('( child.ipTree NOT LIKE CONCAT( parent.ipTree ,\'.\', child.id ) OR child.ipTree IS NULL OR child.ipTree = \'\')')
            ->andWhere('( ( child.sourceDoodleId IS NULL AND child.id != child.ipTree ) OR child.sourceDoodleId IS NOT NULL )')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param $doodleId
     * @return array|int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function generateDoodleIpTree( int $doodleId )
    {
        if( !isset($doodleIpTreeArray) || !is_array($doodleIpTreeArray) )
            $doodleIpTreeArray = [];

        $doodleData = $this->findOne($doodleId);
        $sourceDoodleId = $doodleData->getSourceDoodleId();

        if( is_numeric( $sourceDoodleId )
            AND $sourceDoodleId > 0
            AND ( !in_array( $sourceDoodleId, $doodleIpTreeArray ) )
        ) {
            $doodleIpTreeArray = $this->generateDoodleIpTree( $sourceDoodleId );
            $doodleIpTreeArray[] = $doodleId;
            return $doodleIpTreeArray;
        }else if( is_numeric( $sourceDoodleId )
            AND $sourceDoodleId > 0
            AND ( in_array( $sourceDoodleId, $doodleIpTreeArray ) )
        ){
            $this->logger->error('Loop in doodle ip tree structure for ip ' . $sourceDoodleId . '.' . implode( '.', $doodleIpTreeArray ));
        }

        $doodleIpTreeArray[] = $doodleId;
        return $doodleIpTreeArray;
    }
}
