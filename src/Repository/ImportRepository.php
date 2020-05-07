<?php

namespace App\Repository;

use App\Entity\Import;
use App\Service\ImportManager;
use App\Service\MacroApplyManager;
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

    /**
     * Supprime une ou plusieurs colonnes choisies par l'utilisateur
     * de la BDD correspondante à un fichier chargé.
     *
     * @param Import $import
     * @param MacroApplyManager $columns
     * @throws \Exception
     */
    public function removeColumns(Import $import, MacroApplyManager $columns)
    {
        $dataBase = $this->getEntityManager()->getConnection();
        $importManager = new ImportManager($this->getEntityManager());
        $schemaAndTableName = $importManager->getSchemaAndTableNames($import);

        $requestSQL = 'ALTER TABLE ' . $schemaAndTableName;

        $columnsToRemove = $columns->getColumns();
        $columnsToRemoveKeys = array_keys($columnsToRemove);

        foreach ($columnsToRemove as $key => $columnName) {
            if ($key !== end($columnsToRemoveKeys)) {
                $requestSQL .= ' DROP COLUMN IF EXISTS ' . $dataBase->quoteIdentifier($columnName) . ', ';
            } else {
                $requestSQL .= ' DROP COLUMN IF EXISTS ' . $dataBase->quoteIdentifier($columnName);
            }
        }

        try {
            $dataBase->executeQuery($requestSQL);
        } catch (\Exception $e) {
            throw new \Exception('Une erreur est survenue.');
        }
    }

}
