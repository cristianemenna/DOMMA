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
    public function createTable(int $importId, string $contextName, RowIterator $sheetColumns)
    {
        $dataBase = $this->getEntityManager()->getConnection();
        // Remplace les espaces ou d'autre caractères dans le nom du contexte pour des underscores
        $schemaName = str_replace(' ', '_', mb_strtolower($contextName));
        $tableName = 'import_'. strval($importId);
        // Crée une table avec le nom 'import_id'
        $dataBase->prepare('CREATE TABLE ' . $schemaName . '.' . $tableName . ' ' . '()')
            ->execute()
            ;

        // Ajoute les colonnes dans la table, selon premier ligne du fichier excel
        foreach ($sheetColumns as $row)
        {
            foreach ($row->getCellIterator() as $cell)
            {
                if (1 == $row->getRowIndex()) {
                    $columnName = str_replace([' ', '(', ')', '/', '-', ','], '_', mb_strtolower($cell->getValue()));
                    $dataBase->prepare(
                        'ALTER TABLE ' . $schemaName . '.' . $tableName . ' 
                                ADD COLUMN ' . $columnName . ' VARCHAR')
                        ->execute();
                }
            }
        }
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
