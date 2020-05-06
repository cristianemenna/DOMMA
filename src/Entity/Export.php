<?php


namespace App\Entity;


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