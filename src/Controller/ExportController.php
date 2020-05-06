<?php

namespace App\Controller;

use App\Entity\Export;
use App\Repository\ImportRepository;
use App\Service\ExportManager;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends AbstractController
{
    /**
     * @Route("/export", name="export")
     */
    public function exportFile(SessionInterface $session, ImportRepository $importRepository, Export $exportForm, ExportManager $exportManager)
    {

        // Recupère l'id de l'import mis en variable de session
        $import = $importRepository->find($session->get('import'));
        $fileName = $import->getFileName();
        // Supprime les quatre derniers caractères du nom du fichier (l'extension)
        $fileName = substr($fileName,0, -4);
        $fileName = $fileName . uniqid() . '.' . $exportForm->getFileType();

        // Recupère le contenu de l'import en BDD
        try {
            $spreadSheet = $exportManager->createSpreadSheet($import);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue. Veuillez réessayer.' );
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
}
