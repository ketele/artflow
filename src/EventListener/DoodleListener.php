<?php
namespace App\EventListener;

use App\Entity\Doodle;
use App\Security\Glide;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class DoodleListener
{
    private $entityManager;
    private $doodleFolder;

    public function __construct(EntityManagerInterface $entityManager, string $doodleFolder)
    {
        $this->entityManager = $entityManager;
        $this->doodleFolder = $doodleFolder;
    }

    public function postLoad(Doodle $doodle, LifecycleEventArgs $event): void
    {
        //ToDo: D violation
        $glide = new Glide();

        $doodle->setUrl($glide->generateUrl($this->doodleFolder . $doodle->getId(), $doodle->getFileName()));
    }
}