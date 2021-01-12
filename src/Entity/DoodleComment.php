<?php

namespace App\Entity;

use App\Repository\DoodleCommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DoodleCommentRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class DoodleComment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class, inversedBy="doodleComments")
     */
    private $user;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity=DoodleComment::class, inversedBy="doodleComments")
     */
    private $parent = null;

    /**
     * @ORM\OneToMany(targetEntity=DoodleComment::class, mappedBy="parent")
     */
    private $doodleComments = [];

    /**
     * @ORM\ManyToOne(targetEntity=Doodle::class, inversedBy="doodleComments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $doodle;

    /**
     * @ORM\Column(type="integer")
     */
    private $createdAt;

    public function __construct()
    {
        $this->doodleComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getParentId(): ?int
    {
        return ($this->parent) ? $this->parent->id : null;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getDoodleComments(): Collection
    {
        return $this->doodleComments;
    }

    public function addDoodleComment(self $doodleComment): self
    {
        if (!$this->doodleComments->contains($doodleComment)) {
            $this->doodleComments[] = $doodleComment;
            $doodleComment->setParent($this);
        }

        return $this;
    }

    public function removeDoodleComment(self $doodleComment): self
    {
        if ($this->doodleComments->removeElement($doodleComment)) {
            // set the owning side to null (unless already changed)
            if ($doodleComment->getParent() === $this) {
                $doodleComment->setParent(null);
            }
        }

        return $this;
    }

    public function getDoodle(): ?Doodle
    {
        return $this->doodle;
    }

    public function setDoodle(?Doodle $doodle): self
    {
        $this->doodle = $doodle;

        return $this;
    }

    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    public function setCreatedAt(int $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $dateTime = new \DateTime();
        $this->createdAt = $dateTime->getTimestamp();
    }
}
