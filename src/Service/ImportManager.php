<?php


namespace App\Service;


use App\Entity\Import;
use Doctrine\ORM\EntityManagerInterface;

class ImportManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getSchemaAndTableNames(Import $import): string
    {
        $dataBase = $this->entityManager->getConnection();

        // Recupère le nom du contexte pour identifier le nom du schema de l'import
        $schemaName = $dataBase->quoteIdentifier($import->getContext()->getTitle() . '_' . $import->getContext()->getId());
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        return $schemaName . '.' . $tableName;
    }

    public function selectAll(Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaAndTable = $this->getSchemaAndTableNames($import);

        try {
            return $dataBase->executeQuery('SELECT * FROM ' . $schemaAndTable);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

}