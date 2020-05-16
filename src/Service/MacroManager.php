<?php


namespace App\Service;


use App\Entity\Import;
use App\Entity\Macro;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\InvalidFieldNameException;
use Doctrine\ORM\EntityManagerInterface;

class MacroManager
{
    private $entityManager;
    private $loadFileManager;
    private $importManager;

    public function __construct(EntityManagerInterface $entityManager, LoadFileManager $loadFileManager, ImportManager $importManager)
    {
        $this->entityManager = $entityManager;
        $this->loadFileManager = $loadFileManager;
        $this->importManager = $importManager;
    }

    /** Vérifie le type de la macro appliquée et exécute la fonction correspondante
     *
     * @param MacroApplyManager $macro
     * @param Import $import
     * @return mixed
     * @throws \Exception
     */
    public function applyMacro(MacroApplyManager $macro, Import $import)
    {
        switch ($macro->getMacro()->getType())
        {
            case 'select-add-columns':
                $this->addQueryColumnsToTable($macro, $import);
                $this->addQueryToTable($macro, $import);
                break;
            case 'select-columns':
                $this->select($macro, $import);
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
            case 'tri':
                $this->sort($macro, $import);
                break;
        }
    }

     /**
     * Requête de type select en BDD
     * @param MacroApplyManager $macro
     * @param Import $import
     * @return mixed[]
     * @throws \Exception
     */
    private function selectColumns(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaAndTableName = $this->importManager->getSchemaAndTableNames($import);

        // Recupère le code de la macro
        $macroCode = $macro->getMacro()->getCode();

        // Création de la requête avec le code de la macro
        $requestSQL = 'SELECT id AS query_id, ' . $macroCode .
                        ' FROM ' . $schemaAndTableName;

        try {
            $statement = $dataBase->executeQuery($requestSQL);
            return $statement->fetchAll();
        // Si l'erreur contient les mots 'ERREUR' et 'LINE'
        // récupère le message entre les deux.
        } catch (\Exception $e) {
            $errorMessage = $this->getSubstringBetween($e->getMessage(), 'ERREUR:', 'LINE');
            if ($errorMessage) {
                throw new \Exception($errorMessage);
            } else {
                throw new \Exception('Une erreur est survenue lors de l\'application de la macro.');
            }
        }
    }

    /**
     * Application de macro de type "Select" pour trier sur colonnes de la BDD
     *
     * @param MacroApplyManager $macro
     * @param Import $import
     * @throws \Exception
     */
    private function select(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaAndTableName = $this->importManager->getSchemaAndTableNames($import);
        $columnsToRemove = $this->selectColumns($macro, $import)[0];

        try {
            $allColumns = $this->importManager->selectAll($import)->fetch();
        } catch (\Exception $e) {
            $errorMessage = $this->getSubstringBetween($e->getMessage(), 'ERREUR:', 'LINE');
            if ($errorMessage) {
                throw new \Exception($errorMessage);
            } else {
                throw new \Exception('Une erreur est survenue lors de l\'application de la macro.');
            }
        }

        $requestSQL = 'ALTER TABLE ' . $schemaAndTableName;

        // Crée un tableau avec les colonnes en commun entre la table en BDD
        // et les colonnes à être supprimées
        $commonColumns = array_intersect(
            $this->transformArray($columnsToRemove), $this->transformArray($allColumns)
        );

        foreach ($allColumns as $columnName => $columnContent) {
            // Ajoute à la requête seulement les colonnes qui sont dans le tableau de colonnes en commun
            if ($columnName !== 'id' && !in_array($columnName, $commonColumns)) {
                $requestSQL .= ' DROP COLUMN IF EXISTS ' . $dataBase->quoteIdentifier($columnName) . ',';
            }
        }
        // Supprime la dernière virgule de la fin de la requête
        $requestSQL = substr($requestSQL,0, -1);

        try {
            $dataBase->executeQuery($requestSQL);
        } catch (\Exception $e) {
            $errorMessage = $this->getSubstringBetween($e->getMessage(), 'ERREUR:', 'LINE');
            if ($errorMessage) {
                throw new \Exception($errorMessage);
            } else {
                throw new \Exception('Une erreur est survenue lors de l\'application de la macro.');
            }
        }
    }

    /** Boucle sur le résultat de la requête de select pour ajouter modifier la table
     *  et ajouter les colonnes en BDD, si elles n'existent pas encore.
     *
     * @param MacroApplyManager $macro
     * @param Import $import
     * @throws \Exception
     */
    private function addQueryColumnsToTable(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaAndTableName = $this->importManager->getSchemaAndTableNames($import);
        $columns = $this->selectColumns($macro, $import)[0];

        $requestSQL = 'ALTER TABLE ' . $schemaAndTableName;

        foreach ($columns as $columnName => $value) {
            // N'ajoute pas la colonne qui contient les id
            if ($columnName !== 'query_id') {
                $requestSQL .= ' ADD COLUMN IF NOT EXISTS ' . $dataBase->quoteIdentifier($columnName) . ' TEXT,';
            }
        }
        // Supprime la virgule après le dernier nombre de colonne
        $requestSQL = substr($requestSQL,0, -1);

        try {
            $dataBase->executeQuery($requestSQL);
        // Si l'erreur contient les mots 'ERREUR' et 'LINE'
        // récupère le message entre les deux.
        } catch (\Exception $e) {
            $errorMessage = $this->getSubstringBetween($e->getMessage(), 'ERREUR:', 'LINE');
            if ($errorMessage) {
                throw new \Exception($errorMessage);
            } else {
                throw new \Exception('Une erreur est survenue lors de l\'application de la macro.');
            }
        }
    }

    /** Modifie la table en BDD pour ajouter le résultat d'un select
     *
     * @param MacroApplyManager $macro
     * @param Import $import
     * @throws \Exception
     */
    private function addQueryToTable(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaAndTableName = $this->importManager->getSchemaAndTableNames($import);

        $content = $this->selectColumns($macro, $import);

        // Boucle sur chaque ligne du résultat de la requête
        foreach ($content as $key => $line) {
            $requestSQL = 'UPDATE ' . $schemaAndTableName . ' SET ';

            // Ajoute les noms des colonnes à modifier sur chaque ligne
            $id = null;
            foreach ($line as $column => $contentValue) {
                // On ne lit pas les données de la première colonne (id)
                if ($column !== 'query_id' && $column !== 'id') {
                    $requestSQL .= $dataBase->quoteIdentifier($column) . ' ' . '=' . ' ' . $dataBase->quote($contentValue) . ', ';
                // Recupère l'id de chaque ligne pour l'utiliser ensuite
                } else {
                    $id = $dataBase->quote($contentValue);
                }
            }
            // Supprime la virgule et le espace de la fin de la requête
            $requestSQL = substr($requestSQL,0, -2);
            // Indique l'id de chaque ligne depuis la première
            $requestSQL .= ' WHERE id = ' . $id;

            try {
                $dataBase->executeQuery($requestSQL);
            // Si l'erreur contient les mots 'ERREUR' et 'LINE'
            // récupère le message entre les deux.
            } catch (\Exception $e) {
                $errorMessage = $this->getSubstringBetween($e->getMessage(), 'ERREUR:', 'LINE');
                if ($errorMessage) {
                    throw new \Exception($errorMessage);
                } else {
                    throw new \Exception('Une erreur est survenue lors de l\'application de la macro.');
                }
            }
        }
    }

    /**
     * Ajout de lignes sur la table correspondante à l'import en BDD
     * selon code de la macro appliquée
     *
     * @param MacroApplyManager $macro
     * @param Import $import
     * @throws \Exception
     */
    private function insert(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaAndTableName = $this->importManager->getSchemaAndTableNames($import);

        // Recupère le code de la macro
        $macroCode = $macro->getMacro()->getCode();

        // Création de la requête avec le code de la macro
        $requestSQL = 'INSERT INTO ' . $schemaAndTableName .
                        ' VALUES ( ' . $macroCode . ')';

        try {
            $dataBase->executeQuery($requestSQL);
        } catch (\Exception $e) {
            $errorMessage = $this->getSubstringBetween($e->getMessage(), 'ERREUR:', 'LINE');
            if ($errorMessage) {
                throw new \Exception($errorMessage);
            } else {
                throw new \Exception('Une erreur est survenue lors de l\'application de la macro.');
            }
        }
    }

    /**
     * Mise à jour de lignes sur la table correspondante à l'import en BDD
     * selon code de la macro appliquée
     *
     * @param MacroApplyManager $macro
     * @param Import $import
     * @throws \Exception
     */
    private function update(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaAndTableName = $this->importManager->getSchemaAndTableNames($import);

        // Recupère le code de la macro
        $macroCode = $macro->getMacro()->getCode();

        // Création de la requête avec le code de la macro
        $requestSQL = 'UPDATE ' . $schemaAndTableName .
                        ' SET ' . $macroCode;

        try {
            $dataBase->executeQuery($requestSQL);
        } catch (\Exception $e) {
            $errorMessage = $this->getSubstringBetween($e->getMessage(), 'ERREUR:', 'LINE');
            if ($errorMessage) {
                throw new \Exception($errorMessage);
            } else {
                throw new \Exception('Une erreur est survenue lors de l\'application de la macro.');
            }
        }
    }

    /**
     * Suppression de lignes de la table correspondante à l'import en BDD
     * selon code de la macro appliquée
     *
     * @param MacroApplyManager $macro
     * @param Import $import
     * @throws \Exception
     */
    private function delete(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaAndTableName = $this->importManager->getSchemaAndTableNames($import);

        // Recupère le code de la macro
        $macroCode = $macro->getMacro()->getCode();

        // Création de la requête avec le code de la macro
        $requestSQL = 'DELETE FROM ' . $schemaAndTableName .
                        ' WHERE ' . $macroCode;

        try {
            $dataBase->executeQuery($requestSQL);
        } catch (\Exception $e) {
            $errorMessage = $this->getSubstringBetween($e->getMessage(), 'ERREUR:', 'LINE');
            if ($errorMessage) {
                throw new \Exception($errorMessage);
            } else {
                throw new \Exception('Une erreur est survenue lors de l\'application de la macro.');
            }
        }
    }

    /**
     * Supprime toutes les lignes de la table et les ajoute à nouveau
     * selon tri de la macro (nom de colonne et ordre souhaité)
     *
     * @param MacroApplyManager $macro
     * @param Import $import
     * @throws \Exception
     */
    private function sort(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaAndTableName = $this->importManager->getSchemaAndTableNames($import);

        // Recupère le code de la macro
        $macroCode = $macro->getMacro()->getCode();

        try {
            $allContent = $dataBase->executeQuery('SELECT * FROM ' . $schemaAndTableName . ' ORDER BY ' . $macroCode);
            $dataBase->executeQuery('DELETE FROM ' . $schemaAndTableName . ' WHERE id >= 1');
        } catch (\Exception $e) {
            $errorMessage = $this->getSubstringBetween($e->getMessage(), 'ERREUR:', 'LINE');
            if ($errorMessage) {
                throw new \Exception($errorMessage);
            } else {
                throw new \Exception('Une erreur est survenue lors de l\'application de la macro.');
            }
        }

        foreach ($allContent as $line) {
            $requestSQL = 'INSERT INTO ' . $schemaAndTableName . ' ' . ' VALUES (';

            foreach ($line as $content) {
                $requestSQL .= $dataBase->quote($content) . ', ';
            }

            // Supprime la virgule et le espace de la fin de la requête
            $requestSQL = substr($requestSQL,0, -2);
            $requestSQL .= ')';

            try {
                $dataBase->executeQuery($requestSQL);
            } catch (\Exception $e) {
                $errorMessage = $this->getSubstringBetween($e->getMessage(), 'ERREUR:', 'LINE');
                if ($errorMessage) {
                    throw new \Exception($errorMessage);
                } else {
                    throw new \Exception('Une erreur est survenue lors de l\'application de la macro.');
                }
            }
        }
    }

    /**
     * Recherche une chaîne de caractères entre deux mots.
     * Utilisée pour l'affichage des messages d'erreur
     *
     * @param string $stringToModify
     * @param string $startString
     * @param string $endString
     * @return false|string
     */
    private function getSubstringBetween(string $stringToModify, string $startString, string $endString)
    {
        $substr = substr($stringToModify, strlen($startString)+strpos($stringToModify, $startString),
            (strlen($stringToModify) - strpos($stringToModify, $endString))*(-1));
        return trim($substr);
    }

    /**
     * Retourne un tableau modifié, avec ses valeurs converties en ses clefs
     *
     * @param array $array
     * @return array
     */
    private function transformArray(array $array)
    {
        $transformedArray = [];
        foreach ($array as $key => $value) {
            $transformedArray[$key] = $key;
        }
        return $transformedArray;
    }

    /**
     * Lors de la suppression d'un utilisateur :
     * Itère sur ses macros et les supprime aussi, si elles ne sont pas partagées avec d'autres utilisateurs
     *
     * @param Macro[] $macros
     */
    public function removeMacrosFromUser($macros)
    {
        foreach ($macros as $macro) {
            if (count($macro->getUsers()) === 1) {
                $this->entityManager->remove($macro);
            }
        }
    }

}