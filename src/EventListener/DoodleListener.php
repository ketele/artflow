<?php
namespace App\EventListener;

use App\Entity\Doodle;
use App\Image\Glide;
use Doctrine\ORM\EntityManagerInterface;

class DoodleListener
{
    private $entityManager;
    private $doodleFolder;

    public function __construct(EntityManagerInterface $entityManager, string $doodleFolder)
    {
        $this->entityManager = $entityManager;
        $this->doodleFolder = $doodleFolder;
    }

    public function postLoad(Doodle $doodle): void
    {
        $glide = new Glide();
        $url = $glide->generateUrl($this->doodleFolder . $doodle->getId(), $doodle->getFileName());

        $doodle->setUrl($url);
    }
}