<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 * @UniqueEntity("username", message="Veuillez choisir un nom d'utilisateur différent")
 */
class Users implements UserInterface, EquatableInterface
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
     * @ORM\Column(type="string")
     */
    private $role;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Macro", mappedBy="users")
     */
    private $macros;

    public function __construct()
    {
        $this->contexts = new ArrayCollection();
        $this->macros = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return [$this->role];
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
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
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    /**
     * @param string $first_name
     * @return $this
     */
    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     * @return $this
     */
    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAttempts(): ?int
    {
        return $this->attempts;
    }

    /**
     * @param int $attempts
     * @return $this
     */
    public function setAttempts(int $attempts): self
    {
        $this->attempts = $attempts;

        return $this;
    }

    /**
     * @return $this
     */
    public function incrementAttempts(): self
    {
        $this->attempts += 1;

        return $this;
    }

    /**
     * @return $this
     */
    public function resetAttempts(): self
    {
        $this->attempts = 0;

        return $this;
    }

    /**
     * Permet de déconnecter l'utilisateur en cas de blockage de compte par l'administrateur
     *
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if ($user instanceof Users) {
            if (($user->getAttempts() !== $this->getAttempts())) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return Collection|Context[]
     */
    public function getContexts(): Collection
    {
        return $this->contexts;
    }

    /**
     * @param Context $context
     * @return $this
     */
    public function addContext(Context $context): self
    {
        if (!$this->contexts->contains($context)) {
            $this->contexts[] = $context;
            $context->addUser($this);
        }

        return $this;
    }

    /**
     * @param Context $context
     * @return $this
     */
    public function removeContext(Context $context): self
    {
        if ($this->contexts->contains($context)) {
            $this->contexts->removeElement($context);
            $context->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|Macro[]
     */
    public function getMacros(): Collection
    {
        return $this->macros;
    }

    /**
     * @param Macro $macro
     * @return $this
     */
    public function addMacro(Macro $macro): self
    {
        if (!$this->macros->contains($macro)) {
            $this->macros[] = $macro;
            $macro->addUser($this);
        }

        return $this;
    }

    /**
     * @param Macro $macro
     * @return $this
     */
    public function removeMacro(Macro $macro): self
    {
        if ($this->macros->contains($macro)) {
            $this->macros->removeElement($macro);
            $macro->removeUser($this);
        }

        return $this;
    }
}
