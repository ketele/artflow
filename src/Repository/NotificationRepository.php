<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    protected $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct($registry, Notification::class);
    }

    public function save(Notification $notification)
    {
        $metadata = $this->entityManager->getClassMetadata(get_class($notification));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_AUTO);
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    public function countUserUnread($user)
    {
        return $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->andWhere('n.user = :val')
            ->andWhere('n.readAt is NULL')
            ->setParameter('val', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function addNotification(array $options): void
    {
        foreach ($options['users'] AS $user) {
            $notification = new Notification();
            $notification->setUser($user);
            $notification->setContent($options['content']);
            $this->save($notification);
        }
    }

    public function setAsRead(?array $notifications)
    {
        $dateTime = new \DateTime();
        $readAt = $dateTime->getTimestamp();

        foreach ($notifications AS $notification) {
            if (empty($notification->getReadAt())) {
                $notification->setReadAt($readAt);
                $this->save($notification);
            }
        }
    }
}
