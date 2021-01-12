<?php

namespace App\Repository;

use App\Entity\DoodleComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DoodleComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method DoodleComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method DoodleComment[]    findAll()
 * @method DoodleComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @property  entityManager
 */
class DoodleCommentRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct($registry, DoodleComment::class);
    }

    public function save(DoodleComment $doodleComment)
    {
        $metadata = $this->entityManager->getClassMetadata(get_class($doodleComment));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_AUTO);
        $this->entityManager->persist($doodleComment);
        $this->entityManager->flush();
    }

    public function getDoodlesComments($params = false){
        $queryBuilder = $this->createQueryBuilder('d');

        $opt = [
            'select' => 'd',
            'where' => [],
            'parameters' => [],
            'order' => [['d.createdAt', 'DESC']],
            'maxResults' => null,
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

        if( $maxResults >= 0 )
            $queryBuilder->setMaxResults($maxResults);

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function getDoodlesCommentsTree($params = false){
        $queryBuilder = $this->createQueryBuilder('d');

        $opt = [
            'select' => 'd',
            'where' => [],
            'parameters' => [],
            'order' => [['d.createdAt', 'DESC']],
            'maxResults' => null,
        ];

        if (!empty($params))
            $opt = array_merge($opt,$params);

        extract($opt);

        $queryBuilder->select($select);

        $whereTemp = $where;

        if( !empty($whereTemp) )
            foreach( $whereTemp as $w )
                $queryBuilder->andWhere($w);

        if( !empty($parameters) )
            foreach( $parameters as $p_key => $p )
                $queryBuilder->setParameter($p_key, $p);

        if( !empty($order) ) {
            foreach ($order as $o)
                $queryBuilder->orderBy($o[0], $o[1]);
        }

        if( $maxResults >= 0 )
            $queryBuilder->setMaxResults($maxResults);

        $query = $queryBuilder->getQuery();

        $results = $query->getResult();
        //$results = $query->getArrayResult();

        foreach($results as $resultKey => $result){
            $whereTemp = ['d.parent = ' . $result->getId()];

            $resultsTemp = $this->getDoodlesCommentsTree([
                'maxResults' => null,
                'where' => $whereTemp,
            ]);

            foreach ($resultsTemp as $item) {

                $results[$resultKey]->addDoodleComment($item);
            }
        }

        return $results;
    }

    // /**
    //  * @return DoodleComment[] Returns an array of DoodleComment objects
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
    public function findOneBySomeField($value): ?DoodleComment
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
