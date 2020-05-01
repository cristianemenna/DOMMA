<?php


namespace App\Service;


use App\Entity\Context;
use App\Entity\Import;
use App\Entity\Log;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;

class LoadFileManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Crée une table dans le schéma du contexte
     * Structure de la table selon les données de l'import
     *
     * @param Import $import
     * @param string $contextName
     * @param RowIterator $sheetRows
     * @throws \Exception
     */
    public function createTable(Import $import, Context $context, RowIterator $sheetRows)
    {
       $dataBase = $this->entityManager->getConnection();
       $schemaName = $dataBase->quoteIdentifier($context->getTitle() . '_' . $context->getId());
       $tableName = $dataBase->quoteIdentifier('import_' . strval($import->getId()));

       $requestSQL = 'CREATE TABLE ' . $schemaName . '.' . $tableName . ' ' . '(id serial primary key';

        foreach ($sheetRows as $row) {
            foreach ($row->getCellIterator() as $cell) {
                // Ajoute les colonnes en BDD seulement pour la première ligne du fichier excel
                if ($row->getRowIndex() === 1) {
                    $columnName = $dataBase->quoteIdentifier($cell->getValue());
                    $requestSQL .= ', ' . $columnName . ' TEXT';
                }
            }
        }

        $requestSQL .= ')';

        try {
            $dataBase->executeQuery($requestSQL);
            $import->setStatus('En cours');
            $this->entityManager->persist($import);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \Exception('La table ne peut pas être créé');
        }

    }

    /**
     * Itère sur chaque ligne du fichier
     * Crée une requête pour ajouter toutes les valeurs de chaque ligne
     *
     * @param int $importId
     * @param string $contextName
     * @param RowIterator $sheetRows
     * @throws Exception
     */
    public function addRows(Import $import, Context $context, RowIterator $sheetRows)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaName = $dataBase->quoteIdentifier($context->getTitle() . '_' . $context->getId());
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        foreach ($sheetRows as $index => $row)
        {
            if ($index > 1) {
                // Décremente l'index pour qu'il corresponde au numéro de la ligne en BDD
                $index -= 1;
                // Crée un début de requête pour chaque ligne
                $requestSQL = 'INSERT INTO ' . $schemaName . '.' . $tableName . ' ' . ' VALUES (' . $index . ', ';
                // Itère entre les colonnes pour concaténer la valeur à la requête
                foreach ($row->getCellIterator() as $key => $cell) {
                    $cellContent = $cell->getCalculatedValue(false);
                    // N'ajoute pas de virgule à la première valeur
                    if ($key === 'A') {
                        $requestSQL .= $dataBase->quote($cellContent);
                    } else {
                        $requestSQL .= ', ' . $dataBase->quote($cellContent);
                    }
                }

                $requestSQL .= ')';

                // D'abord essai d'exécuter la requête SQL pour ajouter toutes les colonnes d'une ligne en BDD
                try {
                    $dataBase->executeQuery($requestSQL);
                // En cas d'erreur :
                } catch (\Exception $e) {
                    // Crée un objet log et l'associe à l'import courant
                    $log = new Log();
                    $log->setImport($import);
                    // Ajoute un message d'erreur au log avec l'index de la ligne qui n'a pas pu être ajoutée
                    $log->setMessage('Erreur dans la ligne numéro ' . $index);
                    $import->addLog($log);
                    $this->entityManager->persist($import);
                    $this->entityManager->flush();
                }
            }
        }
        
        // Si l'import contient des objets Log associés, le status de l'import devient 'Fini avec erreur'
        if (count($import->getLogs()) > 0) {
            $import->setStatus('Fini avec erreur');
        // Si l'import ne contient pas d'objets Log, status = 'Fini'
        } else {
            $import->setStatus('Fini');
        }

        $this->entityManager->persist($import);
        $this->entityManager->flush();
    }

    /**
     * Retourne la premier ligne de la table associée à un import
     * ou son contenu complet, selon variable $content
     *
     * @param Import $import
     * @param string $content
     * @return \Doctrine\DBAL\Driver\Statement|mixed
     * @throws DBALException
     */
    public function showTable(Import $import, string $content)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaName = $dataBase->quoteIdentifier($import->getContext()->getTitle() . '_' . $import->getContext()->getId());
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        $statement = $dataBase->prepare('SELECT * FROM ' . $schemaName . '.' . $tableName);

        try {
            $statement->execute();
            if ($content === 'columns') {
                return $statement->fetch();
            } else {
                return $statement;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

    }

}