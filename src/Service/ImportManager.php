<?php


namespace App\Service;


use App\Entity\Import;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\ORM\EntityManagerInterface;

class ImportManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Retourne le nom de la table correspondante à l'import
     * en format : "nom_du_schéma"."nom_de_la_table"
     *
     * @param Import $import
     * @return string
     */
    public function getSchemaAndTableNames(Import $import): string
    {
        $dataBase = $this->entityManager->getConnection();

        // Recupère le nom du contexte pour identifier le nom du schema de l'import
        $schemaName = $dataBase->quoteIdentifier($import->getContext()->getTitle() . '_' . $import->getContext()->getId());
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        return $schemaName . '.' . $tableName;
    }

    /**
     * Exécute un SELECT * sur l'import en BDD
     *
     * @param Import $import
     * @return ResultStatement
     * @throws \Exception
     */
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