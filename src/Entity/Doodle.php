<?php

namespace App\Entity;

use App\Repository\DoodleRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * @ORM\Entity(repositoryClass=DoodleRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Doodle
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $fileName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $userName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var DoodleStatus
     * @ORM\ManyToOne(targetEntity=DoodleStatus::class, inversedBy="doodles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $coordinates = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sourceDoodleId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): ?int
    {
       return $this->id = $id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

    public function setStatus(DoodleStatus $doodleStatus = null): self
    {
        $this->status = $doodleStatus;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @param LifecycleEventArgs $args
     */
    public function setStatusValue(LifecycleEventArgs $args)
    {
        $objectManager = $args->getObjectManager();

        $status = $objectManager->getRepository(DoodleStatus::class)->find(DoodleStatus::STATUS_NEW);
        $this->setStatus($status);
    }

    public function getCoordinates(): ?array
    {
        return $this->coordinates;
    }

    public function setCoordinates(?array $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    public function getSourceDoodleId(): ?int
    {
        return $this->sourceDoodleId;
    }

    public function setSourceDoodleId(?int $sourceDoodleId): self
    {
        $this->sourceDoodleId = $sourceDoodleId;

        return $this;
    }
}