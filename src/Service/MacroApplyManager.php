<?php


namespace App\Service;


class MacroApplyManager
{
    private $macro;
    private $columns;

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getMacro()
    {
        return $this->macro;
    }

    /**
     * @param mixed $macro
     */
    public function setMacro($macro): void
    {
        $this->macro = $macro;
    }

    /**
     * @return mixed
     */
    public function getColumns(): ?array
    {
        return $this->columns;
    }

    /**
     * @param mixed $columns
     */
    public function setColumns($columns): void
    {
        $this->columns = $columns;
    }

}