<?php

namespace App\Repository;

use App\Entity\Import;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;

/**
 * @method Import|null find($id, $lockMode = null, $lockVersion = null)
 * @method Import|null findOneBy(array $criteria, array $orderBy = null)
 * @method Import[]    findAll()
 * @method Import[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Import::class);
    }

    // Crée une table dans le schéma du contexte
    // Structure de la table selon les données de l'import
    public function createTable(int $importId, string $contextName, RowIterator $sheetRows)
    {
        $dataBase = $this->getEntityManager()->getConnection();
        // Remplace les espaces ou d'autre caractères dans le nom du contexte pour des underscores
        $schemaName = str_replace(' ', '_', mb_strtolower($contextName));
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
                    $columnName = str_replace([' ', '(', ')', '/', '-', ','], '_', mb_strtolower($cell->getValue()));
                    $dataBase->prepare(
                        'ALTER TABLE ' . $schemaName . '.' . $tableName . ' 
                                ADD COLUMN ' . $columnName . ' VARCHAR')
                        ->execute();
                }
            }
        }
        $import = $this->find($importId)->setStatus('En cours');
        $this->getEntityManager()->persist($import);
        $this->getEntityManager()->flush();
    }

    // Itère sur chaque ligne du fichier
    // Crée une requête pour ajouter toutes les valeurs de chaque ligne
    public function addRows(int $importId, string $contextName, RowIterator $sheetRows)
    {
        $dataBase = $this->getEntityManager()->getConnection();
        $schemaName = str_replace(' ', '_', mb_strtolower($contextName));
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
                $dataBase->prepare($requestSQL)->execute();
            }
        }
    }

    public function showTable(Import $import)
    {
        $dataBase = $this->getEntityManager()->getConnection();
        $schemaName = str_replace(' ', '_', mb_strtolower($import->getContext()->getTitle()));
        $tableName = 'import_'. strval($import->getId());

        $statement = $dataBase->prepare('SELECT * FROM ' . $schemaName . '.' . $tableName);
        $statement->execute();
        return $statement;
    }


    // /**
    //  * @return Import[] Returns an array of Import objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Import
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
