<?php


namespace App\Service;


class MacroApplyManager
{
    private $macro;

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
}