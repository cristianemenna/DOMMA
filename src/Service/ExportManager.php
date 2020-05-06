<?php


namespace App\Service;


use App\Entity\Import;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExportManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Création d'un fichier avec le contenu de la BDD
     * à l'aide de PhpSpreadsheet
     *
     * @param Import $import
     * @return Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createSpreadSheet(Import $import)
    {
        try {
            $importContent =$this->selectAllFromImport($import);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $spreadSheet = new Spreadsheet();
        $sheet = $spreadSheet->getActiveSheet();

        // Ajout des colonnes
        $columnLetter = 'A'; // Correspond à la première colonne du fichier
        foreach ($importContent[0] as $columnName => $rowContent){
            $sheet->setCellValue($columnLetter . '1', $columnName);
            $columnLetter++;
        }

        // Ajout du contenu sur chaque ligne
        $i = 2; // Correspond à la ligne où le contenu doit commencer à être rajouté
        foreach ($importContent as $line) {
            $columnLetter = 'A';
            foreach ($line as $columnName => $rowContent) {
                $sheet->setCellValue($columnLetter. $i, $rowContent);
                $columnLetter++;
            }
            $i++;
        }

        return $spreadSheet;
    }

    /**
     * Requête pour récupérer tout le contenu d'une table associé à un import
     *
     * @param Import $import
     * @return mixed[]
     * @throws \Exception
     */
    private function selectAllFromImport(Import $import)
    {
        $dataBase = $this->entityManager->getConnection();

        // Recupère le nom du contexte pour identifier le nom du schema de l'import
        $schemaName = $dataBase->quoteIdentifier($import->getContext()->getTitle() . '_' . $import->getContext()->getId());
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_' . strval($import->getId()));

        $requestSQL = 'SELECT * FROM ' . $schemaName . '.' . $tableName;

        try {
            return $dataBase->executeQuery($requestSQL)->fetchAll();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}