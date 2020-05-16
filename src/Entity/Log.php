<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Les logs sont utilisés pour sauvegarder l'information des lignes qui n'ont pas pu être
 * lues lors du chargement des données d'un fichier en BDD
 *
 * @ORM\Entity(repositoryClass="App\Repository\LogRepository")
 */
class Log
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Import", inversedBy="logs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $import;

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return Import|null
     */
    public function getImport(): ?Import
    {
        return $this->import;
    }

    /**
     * @param Import|null $import
     * @return $this
     */
    public function setImport(?Import $import): self
    {
        $this->import = $import;

        return $this;
    }
}
