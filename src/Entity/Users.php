<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 */
class Users implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
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
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $first_name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $last_name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $attempts;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Context", mappedBy="users")
     */
    private $contexts;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Macros", mappedBy="users")
     */
    private $macros;

    public function __construct()
    {
        $this->contexts = new ArrayCollection();
        $this->macros = new ArrayCollection();
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

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        // $roles[] = 'ROLE_USER';

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

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getAttempts(): ?int
    {
        return $this->attempts;
    }

    public function setAttempts(int $attempts): self
    {
        $this->attempts = $attempts;

        return $this;
    }

    public function incrementAttempts(): self
    {
        $this->attempts += 1;

        return $this;
    }

    public function resetAttempts(): self
    {
        $this->attempts = 0;

        return $this;
    }

    /**
     * @return Collection|Context[]
     */
    public function getContexts(): Collection
    {
        return $this->contexts;
    }

    public function addContext(Context $context): self
    {
        if (!$this->contexts->contains($context)) {
            $this->contexts[] = $context;
            $context->addUser($this);
        }

        return $this;
    }

    public function removeContext(Context $context): self
    {
        if ($this->contexts->contains($context)) {
            $this->contexts->removeElement($context);
            $context->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|Macros[]
     */
    public function getMacros(): Collection
    {
        return $this->macros;
    }

    public function addMacro(Macros $macro): self
    {
        if (!$this->macros->contains($macro)) {
            $this->macros[] = $macro;
            $macro->addUser($this);
        }

        return $this;
    }

    public function removeMacro(Macros $macro): self
    {
        if ($this->macros->contains($macro)) {
            $this->macros->removeElement($macro);
            $macro->removeUser($this);
        }

        return $this;
    }
}
