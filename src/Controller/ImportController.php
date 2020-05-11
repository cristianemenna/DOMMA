<?php

namespace App\Controller;

use App\Entity\Import;
use App\Form\ExportType;
use App\Form\MacroApplyType;
use App\Form\MacroColumnsType;
use App\Repository\ImportRepository;
use App\Service\LoadFileManager;
use App\Service\MacroApplyManager;
use App\Service\MacroManager;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManagerInterface;
use Gravatar\Gravatar;
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
    public function show(Request $request,
                         Import $import,
                         Gravatar $gravatar,
                         LoadFileManager $loadFileManager,
                         MacroManager $macroManager,
                         ImportRepository $importRepository,
                         SessionInterface $session): Response
    {
        $user = $this->getUser();
        // Si l'utilisateur actif n'as pas droit d'accès au contexte auquel appartient l'import, on affiche un 'Not found'
        if (!$import->getContext()->getUsers()->contains($user)) {
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
        $columns = new MacroApplyManager();

        $macrosForm = $this->createForm(MacroApplyType::class, $macro, ['macros' => $user->getMacros()]);
        $macrosForm->handleRequest($request);

        // Traitements lors de l'envoi du formulaire d'application d'une macro
        if ($macrosForm->isSubmitted() && $macrosForm->isValid()) {
                try {
                    $macroManager->applyMacro($macro, $import);
                    $importContent = $loadFileManager->showTable($import, 'content');
                    $importColumns = $loadFileManager->showTable($import, 'columns');
                    $this->addFlash('success', 'La macro a bien été appliquée.');
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
        }

        $columnsForm = $this->createForm(MacroColumnsType::class, $columns, ['columns' => $importColumns]);
        $columnsForm->handleRequest($request);

        // Traitement lors de l'envoi du formulaire de suppression de colonnes
        if ($columnsForm->isSubmitted() && $columnsForm->isValid()) {
            try {
                $importRepository->removeColumns($import, $columns);
                $importContent = $loadFileManager->showTable($import, 'content');
                $importColumns = $loadFileManager->showTable($import, 'columns');
                $this->addFlash('success', 'Les colonnes ont bien été supprimées.');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $exportForm = $this->createForm(ExportType::class);
        $exportForm->handleRequest($request);
        
        // Traitement lors de l'envoi du formulaire de téléchargement de fichier
        if ($exportForm->isSubmitted() && $exportForm->isValid()) {
            $response = $this->forward('App\Controller\ExportController::exportFile', [
                'exportForm' => $exportForm->getData(),
            ]);
            return $response;
        }

        return $this->render('import/show.html.twig', [
            'avatar' => $gravatar->avatar($user->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
            'import' => $import,
            'context'=> $import->getContext(),
            'importContent' => $importContent,
            'importColumns' => $importColumns,
            'macros' => $macros,
            'macroForm' => $macrosForm->createView(),
            'exportForm' => $exportForm->createView(),
            'columnsForm' => $columnsForm->createView(),
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
