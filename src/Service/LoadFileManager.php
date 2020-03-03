<?php


namespace App\Service;


use App\Entity\Import;
use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;

class LoadFileManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Crée une table dans le schéma du contexte
    // Structure de la table selon les données de l'import
    public function createTable(int $importId, string $contextName, RowIterator $sheetRows)
    {
        $dataBase = $this->entityManager->getConnection();
        // Remplace les espaces ou d'autre caractères dans le nom du contexte pour des underscores
        $schemaName = str_replace([' ', '(', ')', '/', '-', ',', '\'', '*', '+', '&', '#', '"', '.', '!', ':', '?', '='], '_', mb_strtolower($contextName));
        $tableName = 'import_'. strval($importId);
        // Crée une table avec le nom 'import_id'
        $dataBase->prepare('CREATE TABLE ' . $schemaName . '.' . $tableName . ' ' . '(id serial primary key)')
            ->execute()
        ;

        foreach ($sheetRows as $row)
        {
            foreach ($row->getCellIterator() as $cell)
            {
                // Ajoute les colonnes en BDD seulement pour la première ligne du fichier excel
                if ($row->getRowIndex() === 1) {
                    $columnName = str_replace([' ', '(', ')', '/', '-', ',', '\'', '*', '+', '&', '#', '"', '.', '!', ':', '?', '='], '_', mb_strtolower($cell->getValue()));
                    $dataBase->prepare(
                        'ALTER TABLE ' . $schemaName . '.' . $tableName . ' 
                                ADD COLUMN ' . $columnName . ' VARCHAR')
                        ->execute();
                }
            }
        }

        $import = $this->entityManager->getRepository(Import::class)->find($importId)->setStatus('En cours');
        $this->entityManager->persist($import);
        $this->entityManager->flush();
    }

    // Itère sur chaque ligne du fichier
    // Crée une requête pour ajouter toutes les valeurs de chaque ligne
    public function addRows(int $importId, string $contextName, RowIterator $sheetRows)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaName = str_replace([' ', '(', ')', '/', '-', ',', '\'', '*', '+', '&', '#', '"', '.', '!', ':', '?', '='], '_', mb_strtolower($contextName));
        $tableName = 'import_'. strval($importId);

        foreach ($sheetRows as $index => $row)
        {
            if ($index > 1)
            {
                // Décremente l'index pour qu'il corresponde au numéro de la ligne en BDD
                $index -= 1;
                // Crée un début de requête pour chaque ligne
                $requestSQL = 'INSERT INTO ' . $schemaName . '.' . $tableName . ' ' . ' VALUES (' . $index . ', ';
                // Itère entre les colonnes pour concaténer la valeur à la requête
                foreach ($row->getCellIterator() as $key => $cell)
                {
                    $cellContent = $cell->getValue();
                    // N'ajoute pas de virgule à la première valeur
                    if ($key === 'A')
                    {
                        $requestSQL .= $dataBase->quote($cellContent);
                    } else
                    {
                        $requestSQL .= ', ' . $dataBase->quote($cellContent);
                    }
                }

                $requestSQL .= ')';

                // D'abord essaie d'exécuter la requête SQL pour ajouter tous les colonnes d'une ligne en BDD
                try
                {
                    $dataBase->prepare($requestSQL)->execute();
                }
                // En cas d'erreur :
                catch (\Exception $e)
                {
                    $import = $this->entityManager->getRepository(Import::class)->find($importId);
                    // Crée un objet log et l'associe à l'import courant
                    $log = new Log();
                    $log->setCreatedAt(new \DateTimeInterface());
                    $log->setImport($import);
                    // Ajoute un message d'erreur au log avec l'index de la ligne qui n'a pas pu être lit
                    $log->setMessage('Erreur dans la ligne numéro ' . $index);
                    $import->addLog($log);
                    $this->entityManager->persist($import);
                    $this->entityManager->flush();
                }

            }
        }
        
        $import = $this->entityManager->getRepository(Import::class)->find($importId);
        // Si l'import contient des objets Log associés, le status de l'import devient 'Fini avec erreur'
        if (count($import->getLogs()) > 0)
        {
            $import->setStatus('Fini avec erreur');
        // Si l'import ne contient pas d'objets Log, status = 'Fini'
        } else
        {
            $import->setStatus('Fini');
        }

        $this->entityManager->persist($import);
        $this->entityManager->flush();
    }

    // Retourne la premier ligne de la table associée à un import
    public function showColumns(Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaName = str_replace([' ', '(', ')', '/', '-', ',', '\'', '*', '+', '&', '#', '"', '.', '!', ':', '?', '='], '_', mb_strtolower($import->getContext()->getTitle()));
        $tableName = 'import_'. strval($import->getId());

        $statement = $dataBase->prepare('SELECT * FROM ' . $schemaName . '.' . $tableName);
        $statement->execute();
        return $statement->fetch();
    }

    // Retourne le contenu complet de la table associée à un import
    public function showTable(Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        $schemaName = str_replace([' ', '(', ')', '/', '-', ',', '\'', '*', '+', '&', '#', '"', '.', '!', ':', '?', '='], '_', mb_strtolower($import->getContext()->getTitle()));
        $tableName = 'import_'. strval($import->getId());

        $statement = $dataBase->prepare('SELECT * FROM ' . $schemaName . '.' . $tableName);
        $statement->execute();
        return $statement;
    }


}