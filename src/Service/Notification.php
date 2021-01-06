<?php

namespace App\Service;

use App\Repository\NotificationRepository;
use Symfony\Component\Security\Core\Security;

class Notification
{
    private $notificationRepository;
    private $securit;

    public function __construct(Security $securit, NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
        $this->securit = $securit;
    }

    public function getUserNotificationCount(): string
    {
        $user = $this->securit->getUser();

        return $this->notificationRepository->count(['readAt' => 'IS NULL', 'user' => $user->getId()]);
    }
}