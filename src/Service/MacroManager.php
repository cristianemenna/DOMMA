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
            case 'tiret-par-espace':
                return $this->replaceHyphenBySpace($macro, $import);
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
        $schemaName = $dataBase->quoteIdentifier(mb_strtolower($import->getContext()->getTitle()));
        $schemaName = str_replace([' '], '_', $schemaName);
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        // Création de la requête avec le code de la macro
        $requestSQL = 'SELECT ' . $macroCode .
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
        $schemaName = $dataBase->quoteIdentifier(mb_strtolower($import->getContext()->getTitle()));
        $schemaName = str_replace([' '], '_', $schemaName);
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        $requestSQL = 'ALTER TABLE ' . $schemaName . '.' . $tableName;

        $columnsKeys = array_keys($columns);
        foreach ($columns as $columnName => $value) {
            if ($columnName !== end($columnsKeys)) {
                $requestSQL .= ' ADD COLUMN IF NOT EXISTS ' . $columnName . ' TEXT,';
            } else {
                $requestSQL .= ' ADD COLUMN IF NOT EXISTS ' . $columnName . ' TEXT';
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
        $schemaName = $dataBase->quoteIdentifier(mb_strtolower($import->getContext()->getTitle()));
        $schemaName = str_replace([' '], '_', $schemaName);
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        // Boucle sur chaque ligne du résultat de la requête
        foreach ($content as $key => $line) {
            $requestSQL = 'UPDATE ' . $schemaName . '.' . $tableName . ' SET (';

            // Ajoute les noms des colonnes à modifier sur chaque ligne
            $lineKeys = array_keys($line);
            foreach ($line as $column => $contentValue) {
                if ($column !== end($lineKeys)) {
                    $requestSQL .= $column . ', ';
                } else {
                    $requestSQL .= $column;
                }
            }

            $requestSQL .= ') = (';

            // Ajoute les valeurs à ajouter sur chaque colonne
            foreach ($line as $column => $contentValue) {
                if ($column !== end($lineKeys)) {
                    $requestSQL .= $contentValue . ', ';
                } else {
                    $requestSQL .= $contentValue;
                }
            }

            // Indique l'id de chaque ligne depuis la première
            $requestSQL .= ') WHERE id = ' . ($key + 1);
            $dataBase->executeQuery($requestSQL);
        }
    }

    private function insert(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        // Recupère le code de la macro
        $macroCode = $macro->getMacro()->getCode();

        // Recupère le nom du contexte pour identifier le nom du schema de l'import
        $schemaName = $dataBase->quoteIdentifier(mb_strtolower($import->getContext()->getTitle()));
        $schemaName = str_replace([' '], '_', $schemaName);
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
        $schemaName = $dataBase->quoteIdentifier(mb_strtolower($import->getContext()->getTitle()));
        $schemaName = str_replace([' '], '_', $schemaName);
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
        $schemaName = $dataBase->quoteIdentifier(mb_strtolower($import->getContext()->getTitle()));
        $schemaName = str_replace([' '], '_', $schemaName);
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        // Création de la requête avec le code de la macro
        $requestSQL = 'DELETE FROM ' . $schemaName . '.' . $tableName .
                        ' WHERE ' . $macroCode;

        $dataBase->executeQuery($requestSQL);
    }

    /** Application de macro qui substitue les tirets par espaces vide dans une colonne
     *
     * @param MacroApplyManager $macro
     * @param Import $import
     * @throws \Doctrine\DBAL\DBALException
     */
    private function replaceHyphenBySpace(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        // Recupère le code de la macro
        $macroCode = $macro->getMacro()->getCode();

        // Recupère le nom du contexte pour identifier le nom du schema de l'import
        $schemaName = $dataBase->quoteIdentifier(mb_strtolower($import->getContext()->getTitle()));
        $schemaName = str_replace([' '], '_', $schemaName);
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        // Création de la requête avec le code de la macro
        $requestSQL = 'UPDATE ' . $schemaName . '.' . $tableName . ' SET ' . $macroCode . ' = REPLACE(' . $macroCode . ", '-', ' ')";
        $dataBase->executeQuery($requestSQL);
    }

}