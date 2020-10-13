<?php

namespace App\Entity;

use App\Repository\DoodleStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DoodleStatusRepository::class)
 */
class DoodleStatus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    const STATUS_NEW = 2;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", options={"default": "true"})
     */
    private $isActive = true;

    /**
     * @ORM\OneToMany(targetEntity=Doodle::class, mappedBy="status", orphanRemoval=false)
     */
    private $doodles;

    public function __construct()
    {
        $this->doodles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection|Doodle[]
     */
    public function getDoodles(): Collection
    {
        return $this->doodles;
    }
}
