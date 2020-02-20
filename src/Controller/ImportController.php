<?php

namespace App\Controller;

use App\Entity\Import;
use App\Repository\ImportRepository;
use App\Service\GravatarManager;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/import")
 */
class ImportController extends AbstractController
{
    /**
     * @Route("/", name="import")
     */
    public function index()
    {
        return $this->render('import/index.html.twig', [
            'controller_name' => 'ImportController',
        ]);
    }

    /**
     * @Route("/{id}", name="import_show", methods={"GET", "POST"})
     */
    public function show(Import $import, Security $security, GravatarManager $gravatar, ImportRepository $importRepository): Response
    {
        $fileName = $import->getFile();
        $filePath = $this->getParameter('kernel.project_dir') . '/var/uploads/' . $fileName;
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filePath);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(true);
        $spreadSheet = $reader->load($filePath);

        $sheetColumns = $spreadSheet->getSheet(0)->getRowIterator();

        echo '<table>';

        foreach ($sheetColumns as $column)
        {
            echo '<tr>';
            foreach ($column->getCellIterator() as $cell)
            {
                echo '<td>';
                print_r($cell->getValue());
                echo '</td>';
            }

            echo '</tr>';
        }

        $importRepository->createTable($import->getId(), $import->getContext()->getTitle(), $sheetColumns);
        echo '</table>';

        return $this->render('import/show.html.twig', [
            'avatar' => $gravatar->getAvatar($security),
            'import' => $fileName,
        ]);
    }

    /**
     * @Route("/{id}", name="import_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Import $import, EntityManagerInterface $entityManager): Response
    {
        $context = $import->getContext();
        if ($this->isCsrfTokenValid('delete'.$import->getId(), $request->request->get('_token'))) {
            $entityManager->remove($import);
            $entityManager->flush();
        }

        return $this->redirectToRoute('context_show', ['id' => $context->getId()]);
    }

}
