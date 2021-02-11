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

    public function findRootByDoodleId(int $id, ?array $order = [['dc.createdAt', 'DESC']], ?int $maxResults = null, int $firstResult = 0)
    {
        $queryBuilder = $this->createQueryBuilder('dc');

        $queryBuilder->select('dc')
            ->where('dc.doodle = ' . $id)
            ->andWhere('dc.parent is NULL');

        if (!empty($order)) {
            foreach ($order AS $o) {
                $queryBuilder->orderBy($o[0], $o[1]);
            }
        }

        if ($maxResults >= 0) {
            $queryBuilder->setMaxResults($maxResults);
        }

        $queryBuilder->setFirstResult($firstResult);

        if (is_numeric($maxResults)) {
            $queryBuilder->setMaxResults($maxResults);
        }

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
}
