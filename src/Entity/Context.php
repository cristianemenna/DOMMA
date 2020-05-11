<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Le context correspond à l'espace de travail sur lequel les utilisateurs
 * peuvent importer un ou plusieurs fichiers.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ContextRepository")
 *
 */
class Context
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Users", inversedBy="contexts")
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Positive(message="Les jours négatifs n'existent pas !")
     */
    private $duration;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Import", mappedBy="context", orphanRemoval=true)
     */
    private $imports;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->duration = 30; // Un contexte a une durée de vie de 30 jours par défault
        $this->created_at = new \DateTime(); // C'est l'heure du moment de la création du contexte par défault
        $this->imports = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Users[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param Users $user
     * @return $this|bool
     */
    public function addUser(Users $user)
    {
        if ($this->users === null || !$this->users->contains($user)) {
            $this->users[] = $user;
            return true;
        }

        return $this;
    }

    /**
     * @param Users $user
     * @return $this
     */
    public function removeUser(Users $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    /**
     * @param \DateTimeInterface $created_at
     * @return $this
     */
    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDuration(): ?int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     * @return $this
     */
    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Calcule la date final du contexte de travail, à partir de la date de création +
     * la durée définie par l'utilisateur. Retourne le nombre de jours restants avant la fin de la durée de vie.
     *
     * @return int
     * @throws \Exception
     */
    public function getDaysToExpire(): int
    {
        $creationDate = clone $this->created_at;
        $duration = $this->duration;
        $finalDate = $creationDate->modify('+' . $duration . ' days');
        $currentDate = new \DateTime();
        $difference = $currentDate->diff($finalDate);

        return $difference->days;
    }

    /**
     * @return Collection|Import[]
     */
    public function getImports(): Collection
    {
        return $this->imports;
    }

    /**
     * @param Import $import
     * @return $this
     */
    public function addImport(Import $import): self
    {
        if (!$this->imports->contains($import)) {
            $this->imports[] = $import;
            $import->setContext($this);
        }

        return $this;
    }

    /**
     * @param Import $import
     * @return $this
     */
    public function removeImport(Import $import): self
    {
        if ($this->imports->contains($import)) {
            $this->imports->removeElement($import);
            if ($import->getContext() === $this) {
                $import->setContext(null);
            }
        }
        return $this;
    }
}
