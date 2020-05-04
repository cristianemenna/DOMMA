<?php

namespace App\Controller;

use App\Entity\Import;
use App\Form\MacroApplyType;
use App\Repository\ImportRepository;
use App\Service\GravatarManager;
use App\Service\LoadFileManager;
use App\Service\MacroApplyManager;
use App\Service\MacroManager;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/contexte/{context}/import")
 */
class ImportController extends AbstractController
{
    /**
     * @Route("/{id}", name="import_show", methods={"GET", "POST"})
     */
    public function show(Request $request, Import $import, GravatarManager $gravatar, LoadFileManager $loadFileManager, MacroManager $macroManager, SessionInterface $session): Response
    {
        $user = $this->getUser();
        // Si l'utilisateur actif n'as pas droit d'accès au contexte auquel appartient l'import, on affiche un 'Not found'
        if (!$import->getContext()->getUsers()->contains($user))
        {
            throw $this->createNotFoundException();
        }

        $session->set('import', $import->getId());
        $macros = $user->getMacros();

        // Affiche un message sur la page du contexte si l'import ne peut pas être affiché
        try {
            $importContent = $loadFileManager->showTable($import, 'content');
            $importColumns = $loadFileManager->showTable($import, 'columns');
        } catch (\Exception $e) {
            // Message d'erreur quand l'utilisateur essaye d'ouvrir un fichier dont l'upload a été réalisé
            // mais dont les données n'ont pas pu être chargées. Notamment lors de l'import de plusieurs fichiers dont un qui génère un souci.
            $this->addFlash('error',
                'Le fichier ' . $import->getFileName() . ' n\'a pas été chargé correctement et a été mis en attente. ');
            return $this->redirectToRoute('context_show', ['id' => $import->getContext()->getId()]);
        }

        $macro = new MacroApplyManager();
        $form = $this->createForm(MacroApplyType::class, $macro, ['macros' => $user->getMacros()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (isset($_POST['details'])) {
                return $this->redirectToRoute('macro_edit', ['id' => $macro->getMacro()->getId()]);
            } else {
                // Exécute la requête en BDD de la macro séléctionnée
                try {
                    $macroManager->applyMacro($macro, $import);
                    $importContent = $loadFileManager->showTable($import, 'content');
                    $importColumns = $loadFileManager->showTable($import, 'columns');
                    $this->addFlash('success', 'La macro a bien été appliquée.');
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('import/show.html.twig', [
            'avatar' => $gravatar->getAvatar($user),
            'import' => $import,
            'importContent' => $importContent,
            'importColumns' => $importColumns,
            'macros' => $macros,
            'macroForm' => $form->createView(),
        ]);
    }

    /**
     * Recharge un fichier dont le état est 'En attente' depuis la page show
     * d'un contexte de travail
     *
     * @Route("/{id}/upload", name="import_reload")
     */
    public function reloadFile(Request $request, Import $import, UploadManager $uploadManager)
    {
        try {
            $uploadManager->readFile($import->getContext());
            $this->addFlash('success', 'Le fichier a bien été chargé.');
        } catch (\Exception $e) {
            $this->addFlash(
                'error', $e->getMessage());
        }

        return $this->redirectToRoute('context_show', ['id' => $import->getContext()->getId()]);
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
