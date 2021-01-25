<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=AdminRepository::class)
 * @UniqueEntity(fields={"username"}, message="admin.username.exists")
 * @UniqueEntity(fields={"email"}, message="admin.email.exists")
 * @ORM\HasLifecycleCallbacks()
 */
class Admin implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="integer")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=Doodle::class, mappedBy="user")
     */
    private $doodles;

    /**
     * @ORM\OneToMany(targetEntity=DoodleComment::class, mappedBy="user")
     */
    private $doodleComments;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $locale;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="user")
     */
    private $tasks;

    /**
     * @ORM\OneToMany(targetEntity=TaskStatus::class, mappedBy="user", orphanRemoval=true)
     */
    private $taskStatuses;

    public function __construct()
    {
        $this->doodles = new ArrayCollection();
        $this->doodleComments = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->taskStatuses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function __toString(): string
    {
        return $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->is_verified;
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

    /**
     * @return Collection|Doodle[]
     */
    public function getDoodles(): Collection
    {
        return $this->doodles;
    }

    public function addDoodle(Doodle $doodle): self
    {
        if (!$this->doodles->contains($doodle)) {
            $this->doodles[] = $doodle;
            $doodle->setUser($this);
        }

        return $this;
    }

    public function removeDoodle(Doodle $doodle): self
    {
        if ($this->doodles->removeElement($doodle)) {
            // set the owning side to null (unless already changed)
            if ($doodle->getUser() === $this) {
                $doodle->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DoodleComment[]
     */
    public function getDoodleComments(): Collection
    {
        return $this->doodleComments;
    }

    public function addDoodleComment(DoodleComment $doodleComment): self
    {
        if (!$this->doodleComments->contains($doodleComment)) {
            $this->doodleComments[] = $doodleComment;
            $doodleComment->setUser($this);
        }

        return $this;
    }

    public function removeDoodleComment(DoodleComment $doodleComment): self
    {
        if ($this->doodleComments->removeElement($doodleComment)) {
            // set the owning side to null (unless already changed)
            if ($doodleComment->getUser() === $this) {
                $doodleComment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->doodleComments;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->doodleComments->contains($notification)) {
            $this->doodleComments[] = $notification;
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->doodleComments->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setUser($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TaskStatus[]
     */
    public function getTaskStatuses(): Collection
    {
        return $this->taskStatuses;
    }

    public function addTaskStatus(TaskStatus $taskStatus): self
    {
        if (!$this->taskStatuses->contains($taskStatus)) {
            $this->taskStatuses[] = $taskStatus;
            $taskStatus->setUser($this);
        }

        return $this;
    }

    public function removeTaskStatus(TaskStatus $taskStatus): self
    {
        if ($this->taskStatuses->removeElement($taskStatus)) {
            // set the owning side to null (unless already changed)
            if ($taskStatus->getUser() === $this) {
                $taskStatus->setUser(null);
            }
        }

        return $this;
    }
}
