<?php

namespace App\Notification;

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
            $count = $this->notificationRepository->countUserUnread($user);
        }

        return $count;
    }
}