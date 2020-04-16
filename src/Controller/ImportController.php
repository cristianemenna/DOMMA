<?php

namespace App\Controller;

use App\Entity\Import;
use App\Form\MacroApplyType;
use App\Repository\ImportRepository;
use App\Service\GravatarManager;
use App\Service\LoadFileManager;
use App\Service\MacroApplyManager;
use App\Service\MacroManager;
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
    public function show(Request $request, Import $import, GravatarManager $gravatar, LoadFileManager $loadFileManager, MacroManager $macroManager): Response
    {
        $connectedUser = $this->getUser();
        $macros = $connectedUser->getMacros();
        $importContent = $loadFileManager->showTable($import);
        $importColumns = $loadFileManager->showColumns($import);

        $macro = new MacroApplyManager();
        $form = $this->createForm(MacroApplyType::class, $macro, ['macros' => $connectedUser->getMacros()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Exécute la requête en BDD de la macro séléctionnée
            $macroManager->applyMacro($macro, $import);
        }

        return $this->render('import/show.html.twig', [
            'avatar' => $gravatar->getAvatar($connectedUser),
            'import' => $import,
            'importContent' => $importContent,
            'importColumns' => $importColumns,
            'macros' => $macros,
            'macroForm' => $form->createView(),
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
