<?php


namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;

class MacroManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function applyMacro(MacroApplyManager $macro)
    {
        switch ($macro->getMacro()->getType())
        {
            case 'select':
                $this->select($macro);
                break;
            case 'update':
                $this->update($macro);
                break;
        }
    }

    public function select($macro)
    {
        dd('bien joué le select');
    }


}