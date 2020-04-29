<?php


namespace App\Service;


use App\Entity\Import;
use Doctrine\ORM\EntityManagerInterface;

class MacroManager
{
    private $entityManager;
    private $loadFileManager;

    public function __construct(EntityManagerInterface $entityManager, LoadFileManager $loadFileManager)
    {
        $this->entityManager = $entityManager;
        $this->loadFileManager = $loadFileManager;
    }

    /** Vérifie le type de la macro appliquée et exécute la fonction correspondante
     *
     * @param MacroApplyManager $macro
     * @param Import $import
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function applyMacro(MacroApplyManager $macro, Import $import)
    {
        switch ($macro->getMacro()->getType())
        {
            case 'select':
                $this->addQueryColumnsToTable($macro, $import);
                $this->addQueryToTable($macro, $import);
                break;
            case 'insert':
                $this->insert($macro, $import);
                break;
            case 'update':
                $this->update($macro, $import);
                break;
            case 'delete':
                $this->delete($macro, $import);
                break;
        }
    }

    /** Application de macro de type "Select"
     *
     * @param MacroApplyManager $macro
     * @param Import $import
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    private function select(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        // Recupère le code de la macro
        $macroCode = $macro->getMacro()->getCode();

        // Recupère le nom du contexte pour identifier le nom du schema de l'import
        $schemaName = $dataBase->quoteIdentifier($import->getContext()->getTitle());
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        // Création de la requête avec le code de la macro
        $requestSQL = 'SELECT id AS query_id, ' . $macroCode .
                        ' FROM ' . $schemaName . '.' . $tableName;
        $statement = $dataBase->executeQuery($requestSQL);

        return $statement->fetchAll();
    }

    /** Boucle sur le résultat de la requête de select pour ajouter modifier la table
     *  et ajouter les colonnes en BDD, s'ils elles n'existent pas encore.
     *
     * @param MacroApplyManager $macro
     * @param Import $import
     * @throws \Doctrine\DBAL\DBALException
     */
    private function addQueryColumnsToTable(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        $columns = $this->select($macro, $import)[0];

        // Recupère le nom du contexte pour identifier le nom du schema de l'import
        $schemaName = $dataBase->quoteIdentifier($import->getContext()->getTitle());
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        $requestSQL = 'ALTER TABLE ' . $schemaName . '.' . $tableName;

        $columnsKeys = array_keys($columns);
        foreach ($columns as $columnName => $value) {
            // N'ajoute pas la colonne qui contient les id
            if ($columnName !== 'query_id') {
                if ($columnName !== end($columnsKeys)) {
                    $requestSQL .= ' ADD COLUMN IF NOT EXISTS ' . $columnName . ' TEXT,';
                } else {
                    $requestSQL .= ' ADD COLUMN IF NOT EXISTS ' . $columnName . ' TEXT';
                }
            }
        }

        $dataBase->executeQuery($requestSQL);
    }

    /** Modifie la table en BDD pour ajouter le résultat d'un select
     *
     * @param MacroApplyManager $macro
     * @param Import $import
     * @throws \Doctrine\DBAL\DBALException
     */
    private function addQueryToTable(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        $content = $this->select($macro, $import);

        // Recupère le nom du contexte pour identifier le nom du schema de l'import
        $schemaName = $dataBase->quoteIdentifier($import->getContext()->getTitle());
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        // Boucle sur chaque ligne du résultat de la requête
        foreach ($content as $key => $line) {
            $requestSQL = 'UPDATE ' . $schemaName . '.' . $tableName . ' SET ';

            // Ajoute les noms des colonnes à modifier sur chaque ligne
            $id = null;
            $lineKeys = array_keys($line);
            foreach ($line as $column => $contentValue) {
                // On ne lit pas les données de la première colonne (id)
                if ($column !== 'query_id') {
                    // Actualise chaque colonne avec la valeur correspondante dans le résultat du select
                    if ($column !== end($lineKeys)) {
                        $requestSQL .= $column . ' ' . '=' . ' ' . "'" . $contentValue . "', ";
                    } else {
                        $requestSQL .= $column . ' ' . '=' . ' ' . "'" . $contentValue . "'";
                    }
                // Recupère l'id de chaque ligne pour l'utiliser ensuite
                } else {
                    $id = $contentValue;
                }
            }

            // Indique l'id de chaque ligne depuis la première
            $requestSQL .= ' WHERE id = ' . $id;
            $dataBase->executeQuery($requestSQL);
        }
    }

    private function insert(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        // Recupère le code de la macro
        $macroCode = $macro->getMacro()->getCode();

        // Recupère le nom du contexte pour identifier le nom du schema de l'import
        $schemaName = $dataBase->quoteIdentifier($import->getContext()->getTitle());
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        // Création de la requête avec le code de la macro
        $requestSQL = 'INSERT INTO ' . $schemaName . '.' . $tableName .
                        ' VALUES ( ' . $macroCode . ')';

        $dataBase->executeQuery($requestSQL);
    }

    private function update(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        // Recupère le code de la macro
        $macroCode = $macro->getMacro()->getCode();

        // Recupère le nom du contexte pour identifier le nom du schema de l'import
        $schemaName = $dataBase->quoteIdentifier($import->getContext()->getTitle());
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        // Création de la requête avec le code de la macro
        $requestSQL = 'UPDATE ' . $schemaName . '.' . $tableName .
                        ' SET ' . $macroCode;

        $dataBase->executeQuery($requestSQL);
    }

    private function delete(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        // Recupère le code de la macro
        $macroCode = $macro->getMacro()->getCode();

        // Recupère le nom du contexte pour identifier le nom du schema de l'import
        $schemaName = $dataBase->quoteIdentifier($import->getContext()->getTitle());
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        // Création de la requête avec le code de la macro
        $requestSQL = 'DELETE FROM ' . $schemaName . '.' . $tableName .
                        ' WHERE ' . $macroCode;

        $dataBase->executeQuery($requestSQL);
    }

}