<?php

namespace App\Service;

use App\Entity\Admin;
use App\Repository\NotificationRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\User;

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
            $count = $this->notificationRepository->countUserUnread($user);
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

    public function setAsRead(?array $notifications){
        $dateTime = new \DateTime();
        $readdAt = $dateTime->getTimestamp();

        foreach( $notifications AS $notification )
            if( empty( $notification->getReadAt() ) ){
                $notification->setReadAt($readdAt);
                $this->notificationRepository->save($notification);
            }
    }
}