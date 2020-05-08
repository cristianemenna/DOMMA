<?php

namespace App\Controller;

use App\Entity\Import;
use App\Entity\Macro;
use App\Form\MacroType;
use App\Repository\ImportRepository;
use App\Repository\MacroRepository;
use Gravatar\Gravatar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/macro")
 */
class MacroController extends AbstractController
{
    /**
     * @Route("/", name="macro_index", methods={"GET"})
     */
    public function index(MacroRepository $macroRepository, Gravatar $gravatar): Response
    {
        return $this->render('macro/index.html.twig', [
            'macros' => $this->getUser()->getMacros(),
            'avatar' => $gravatar->avatar($this->getUser()->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
        ]);
    }

    /**
     * @Route("/new", name="macro_new", methods={"GET","POST"})
     */
    public function new(Request $request, Gravatar $gravatar, SessionInterface $session, ImportRepository $importRepository): Response
    {
        // Recupère l'id de l'import de la page d'origine
        $import = $importRepository->find($session->get('import'));
        $macro = new Macro();
        $form = $this->createForm(MacroType::class, $macro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $macro->addUser($this->getUser());
            $entityManager->persist($macro);
            $entityManager->flush();

            return $this->redirectToRoute('import_show', ['context' => $import->getContext()->getId(), 'id' => $import->getId()]);
        }

        return $this->render('macro/new.html.twig', [
            'macro' => $macro,
            'context' => $import->getContext(),
            'form' => $form->createView(),
            'import' => $import,
            'avatar' => $gravatar->avatar($this->getUser()->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
        ]);
    }

    /**
     * @Route("/{id}", name="macro_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Macro $macro, Gravatar $gravatar, SessionInterface $session, ImportRepository $importRepository): Response
    {
        // Si l'utilisateur actif n'as pas droit d'accès à la macro, on affiche page 403'
        $this->denyAccessUnlessGranted('edit', $macro);
        $form = $this->createForm(MacroType::class, $macro);
        $form->handleRequest($request);

        if ($session->get('import')) {
            $import = $importRepository->find($session->get('import'));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            // Une fois la macro modifiée :
            // Redirection sur la page de l'import s'il y a une variable 'import' stockée en session
            if ($session->get('import')) {
                return $this->redirectToRoute('import_show',
                    ['context' => $import->getContext()->getId(),
                        'id' => $session->get('import')]);
            // Sinon redirection sur la page de toutes les macros
            } else {
                return $this->redirectToRoute('macro_index');
            }
        }

        return $this->render('macro/edit.html.twig', [
            'macro' => $macro,
            'form' => $form->createView(),
            'avatar' => $gravatar->avatar($this->getUser()->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
        ]);
    }

    /**
     * @Route("/{id}", name="macro_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Macro $macro): Response
    {
        if ($this->isCsrfTokenValid('delete'.$macro->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($macro);
            $entityManager->flush();
        }

        return $this->redirectToRoute('macro_index');
    }
}
