<?php


namespace App\Service;


use App\Entity\Export;
use App\Entity\Import;
use App\Repository\ImportRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportManager
{
    private $entityManager;
    private $importManager;
    private $importRepository;

    public function __construct(EntityManagerInterface $entityManager,
                                ImportManager $importManager,
                                ImportRepository $importRepository)
    {
        $this->entityManager = $entityManager;
        $this->importManager = $importManager;
        $this->importRepository = $importRepository;
    }


    /**
     * Récupère les données en BDD, crée un fichier Excel avec et l'envoie au client pour téléchargement
     *
     * @param Export $exportForm
     * @param Import $import
     * @return StreamedResponse
     * @throws \Exception
     */
    public function exportFile(Export $exportForm, Import $import)
    {
        $fileName = $import->getFileName();
        // Supprime les quatre derniers caractères du nom du fichier (l'extension)
        $fileName = substr($fileName,0, -4);
        $fileName = $fileName . uniqid() . '.' . $exportForm->getFileType();

        // Recupère le contenu de l'import en BDD
        try {
            $spreadSheet = $this->createSpreadSheet($import);
        } catch (\Exception $e) {
            throw new \Exception('Une erreur inconnue est survenue. Veuillez réessayer.');
        }

        // Vérifie le format de fichier choisi par l'utilisateur
        switch ($exportForm->getFileType()) {
            case 'xls':
                $contentType = 'application/vnd.ms-excel';
                $writer = new Xls($spreadSheet);
                break;
            case 'xlsx':
                $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                $writer = new Xlsx($spreadSheet);
                break;
            case 'csv':
                $contentType = 'text/csv';
                $writer = new Csv($spreadSheet);
                break;
            default:
        }

        // Envoie du fichier au navigateur
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', $contentType);
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });

        return $response;
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