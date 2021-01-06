<?php

namespace App\Service;

use App\Repository\NotificationRepository;
use Symfony\Component\Security\Core\Security;

class Notification
{
    private $notificationRepository;
    private $security;

    public function __construct(Security $security, NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
        $this->security = $security;
    }

    public function getUserNotificationCount(): string
    {
        $count = 0;

        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->security->getUser();
            $count = $this->notificationRepository->count(['readAt' => 'IS NULL', 'user' => $user->getId()]);
        }

        return $count;
    }

    public function addNotification(array $options): void
    {
        foreach( $options['users'] AS $user ) {
            $notification = new \App\Entity\Notification();
            $notification->setUser($user);
            $notification->setContent($options['content']);
            $this->notificationRepository->save($notification);
        }
    }
}