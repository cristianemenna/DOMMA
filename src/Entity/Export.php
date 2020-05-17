<?php


namespace App\Entity;

/**
 * Entité qui récupère le type de fichier à être utilisé pour l'export des données
 * vers un fichier Excel.
 *
 * @package App\Entity
 */
class Export
{
    private $fileType;

    /**
     * @return string|null
     */
    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    /**
     * @param string $fileType
     */
    public function setFileType(string $fileType)
    {
        $this->fileType = $fileType;
    }
}