<?php


namespace App\Service;


use App\Entity\Import;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExportManager
{
    private $entityManager;
    private $importManager;

    public function __construct(EntityManagerInterface $entityManager, ImportManager $importManager)
    {
        $this->entityManager = $entityManager;
        $this->importManager = $importManager;
    }

    /**
     * Création d'un fichier avec le contenu de la BDD
     * à l'aide de PhpSpreadsheet
     *
     * @param Import $import
     * @return Spreadsheet
     * @throws Exception
     */
    public function createSpreadSheet(Import $import)
    {
        try {
            $importContent =$this->importManager->selectAll($import)->fetchAll();
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
}