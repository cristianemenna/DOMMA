<?php


namespace App\Service;

/**
 * Utilisée pour récupérer un objet Macro ou un tableau de colonnes
 * lors de l'application de MacroApplyType | MacroColumnsType
 *
 * @package App\Service
 */
class MacroApplyManager
{
    private $macro;
    private $columns;

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