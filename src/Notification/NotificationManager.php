<?php

namespace App\Notification;

use App\Repository\NotificationRepository;

class NotificationManager
{
    private $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function addNotification(array $options): void
    {
        foreach ($options['users'] AS $user) {
            $notification = new \App\Entity\Notification();
            $notification->setUser($user);
            $notification->setContent($options['content']);
            $this->notificationRepository->save($notification);
        }
    }

    public function setAsRead(?array $notifications)
    {
        $dateTime = new \DateTime();
        $readdAt = $dateTime->getTimestamp();

        foreach ($notifications AS $notification) {
            if (empty($notification->getReadAt())) {
                $notification->setReadAt($readdAt);
                $this->notificationRepository->save($notification);
            }
        }
    }
}