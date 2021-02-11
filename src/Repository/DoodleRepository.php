<?php

namespace App\Repository;

use App\Entity\Admin;
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
            ->getResult();
    }

    public function findByStatusTheMostPopular($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.status = :val')
            ->setParameter('val', $value)
            ->orderBy('d.popularity', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    public function findByFilter(?array $params)
    {
        $where = [];
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('d');

        if(isset($params['status']) && is_numeric($params['status'])) {
            $where[] = 'd.status = ' . $params['status'];
        }

        if (!empty($where)) {
            foreach ($where as $w) {
                $queryBuilder->andWhere($w);
            }
        }

        if (empty($params['order'])) {
            $params['order'] = [['d.popularity', 'DESC']];
        }

        foreach ($params['order'] as $order) {
            $queryBuilder->orderBy($order[0], $order[1]);
        }

        if(isset($params['maxResults']) && is_numeric($params['maxResults'])) {
            $queryBuilder->setMaxResults($params['maxResults']);
        }

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function findPublished(?array $order = [['d.popularity', 'DESC']],?int $maxResults = null,int $firstResult = 0)
    {
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('d')
            ->where('d.status = ' . DoodleStatus::STATUS_PUBLISHED);

        if (!empty($order)) {
            foreach ($order as $o) {
                $queryBuilder->orderBy($o[0], $o[1]);
            }
        }

        $queryBuilder->setFirstResult($firstResult);

        if(is_numeric($maxResults)) {
            $queryBuilder->setMaxResults($maxResults);
        }

        $query = $queryBuilder->getQuery();

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

        while (!empty($doodle)) {
            $doodle_id = $doodle->getId();

            if (!in_array($doodle_id, $updated_doodles)) {
                $updated_doodles[] = $doodle_id;

                $this->_user_ip_tree = array();
                $ip_tree = $this->generateDoodleIpTree($doodle_id);
                $doodle->setIpTree(implode('.', $ip_tree));
                $this->entityManager->persist($doodle);
                $this->entityManager->flush();

                unset($doodle);
                $doodle = $this->findWrongIpTreeRow(array((!empty($updated_doodles))
                    ? 'child.id NOT IN ( ' . implode(',', $updated_doodles) . ' )'
                    : 'child.id = child.id'));
            }
        }

        return true;
    }

    public function findWrongIpTreeRow($where = array())
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        if (!empty($where)) {
            foreach ($where as $w) {
                $queryBuilder->andWhere($w);
            }
        }

        $query = $queryBuilder->select('child')
            ->from(Doodle::class, 'child')
            ->leftJoin(Doodle::class, 'parent', \Doctrine\ORM\Query\Expr\Join::WITH, 'parent.id = child.sourceDoodleId')
            ->andWhere('( child.ipTree NOT LIKE CONCAT( parent.ipTree ,\'.\', child.id ) OR child.ipTree IS NULL OR child.ipTree = \'\')')
            ->andWhere('( ( child.sourceDoodleId IS NULL AND child.id != child.ipTree ) OR child.sourceDoodleId IS NOT NULL )')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param int $doodleId
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function generateDoodleIpTree(int $doodleId)
    {
        if (!isset($doodleIpTreeArray) || !is_array($doodleIpTreeArray)) {
            $doodleIpTreeArray = [];
        }

        $doodleData = $this->findOne($doodleId);
        $sourceDoodleId = $doodleData->getSourceDoodleId();

        if (is_numeric($sourceDoodleId)
            && $sourceDoodleId > 0
            && (!in_array($sourceDoodleId, $doodleIpTreeArray))
        ) {
            $doodleIpTreeArray = $this->generateDoodleIpTree($sourceDoodleId);
            $doodleIpTreeArray[] = $doodleId;
            return $doodleIpTreeArray;
        } else if (is_numeric($sourceDoodleId)
            && $sourceDoodleId > 0
            && (in_array($sourceDoodleId, $doodleIpTreeArray))
        ) {
            $this->logger->error('Loop in doodle ip tree structure for ip ' . $sourceDoodleId . '.' . implode('.', $doodleIpTreeArray));
        }

        $doodleIpTreeArray[] = $doodleId;
        return $doodleIpTreeArray;
    }

    public function findRecommended(int $id, ?int $doodlesCount = 3)
    {
        $doodle = $this->findOne($id);

        $queryBuilder = $this->createQueryBuilder('d')
            ->select('d')
            ->where('d.id != :parentDoodle')
            ->andWhere('( d.ipTree LIKE :parentDoodleIpTreeBegin OR d.ipTree LIKE :parentDoodleIpTree )')
            ->andWhere('d.status = ' . DoodleStatus::STATUS_PUBLISHED)
            ->setParameter('parentDoodle', $id)
            ->setParameter('parentDoodleIpTreeBegin', $id)
            ->setParameter('parentDoodleIpTree', $id)
            ->orderBy('d.popularity', 'DESC')
            ->setMaxResults($doodlesCount);
        $query = $queryBuilder->getQuery();
        $doodles = $query->getResult();
        unset($queryBuilder);

        if (count($doodles) < $doodlesCount) {
            $queryBuilder = $this->createQueryBuilder('d')
                ->select('d, ABS(DATE_DIFF( d.createdAt, :parentCreatedAt )) AS HIDDEN score')
                ->where('d.id NOT IN(:doodles)')
                ->andWhere('d.status = ' . DoodleStatus::STATUS_PUBLISHED)
                ->setParameter('doodles', $id . (count($doodles) > 0 ? ',' . implode(array_map(function ($v) {
                        return $v->getId();
                    }, $doodles)) : ''))
                ->setParameter('parentCreatedAt', $doodle->getCreatedAt())
                ->orderBy('score', 'ASC')
                ->setMaxResults($doodlesCount- count($doodles));
            $query = $queryBuilder->getQuery();
            $doodlesTemp = $query->getResult();

            $doodles = array_merge($doodles, $doodlesTemp);
        }

        return $doodles;
    }

    public function findSimilar(int $id, ?array $order = [['d.popularity', 'DESC']],?int $maxResults = null,int $firstResult = 0)
    {
        $queryBuilder = $this->createQueryBuilder('d');

        $rootDoodle = $this->findOne($id);
        $ipTree = $rootDoodle->getIpTree();
        $rootId = explode('.', $ipTree)[0];

        $queryBuilder->select('d')
            ->where('d.status = ' . DoodleStatus::STATUS_PUBLISHED)
            ->andWhere('( d.id = :doodleId OR d.ipTree LIKE :doodleIdBegin OR d.ipTree LIKE :doodleIdInner OR d.ipTree LIKE :doodleIdEnd)')
            ->setParameter('doodleId', $rootId)
            ->setParameter('doodleIdBegin', $rootId . '.%')
            ->setParameter('doodleIdInner', '%.' . $rootId . '.%')
            ->setParameter('doodleIdEnd', '%.' . $rootId);

        if (!empty($order)) {
            foreach ($order as $o) {
                $queryBuilder->orderBy($o[0], $o[1]);
            }
        }

        $queryBuilder->setFirstResult($firstResult);

        if(is_numeric($maxResults)) {
            $queryBuilder->setMaxResults($maxResults);
        }

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function findUsers(Admin $user, ?array $order = [['d.popularity', 'DESC']],?int $maxResults = null,int $firstResult = 0)
    {
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('d')
            ->where('d.user = ' . $user->getId());

        if (!empty($order)) {
            foreach ($order as $o) {
                $queryBuilder->orderBy($o[0], $o[1]);
            }
        }

        $queryBuilder->setFirstResult($firstResult);

        if(is_numeric($maxResults)) {
            $queryBuilder->setMaxResults($maxResults);
        }

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
}
