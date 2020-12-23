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

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    private $url;

    /**
     * @ORM\Column(type="integer", options={"default": "0"})
     */
    private $popularity = 0;

    /**
     * @ORM\Column(type="integer", options={"default": "0"})
     */
    private $views = 0;

    /**
     * @ORM\Column(type="text")
     */
    private $ipTree = '';

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class, inversedBy="doodles")
     */
    private $user;

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

    public function getStatus(): ?DoodleStatus
    {
        return $this->status;
    }

    /**
     * @return array|null
     */
    public function getCoordinates(): ?array
    {
        return $this->coordinates;
    }

    /**
     * @param array|null $coordinates
     * @return Doodle
     */
    public function setCoordinates(?array $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSourceDoodleId(): ?int
    {
        return $this->sourceDoodleId;
    }

    /**
     * @param int|null $sourceDoodleId
     * @return Doodle
     */
    public function setSourceDoodleId(?int $sourceDoodleId): self
    {
        $this->sourceDoodleId = $sourceDoodleId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     * @return Doodle
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getPopularity(): ?int
    {
        return $this->popularity;
    }

    public function setPopularity(int $popularity): self
    {
        $this->popularity = $popularity;

        return $this;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): self
    {
        $this->views = $views;

        return $this;
    }

    public function getIpTree(): ?string
    {
        return $this->ipTree;
    }

    public function setIpTree(string $ipTree): self
    {
        $this->ipTree = $ipTree;

        return $this;
    }

    public function getUser(): ?Admin
    {
        return $this->user;
    }

    public function setUser(?Admin $user): self
    {
        $this->user = $user;

        return $this;
    }
}
