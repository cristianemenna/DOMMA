<?php

namespace App\Controller;

use App\Entity\Macro;
use App\Form\MacroType;
use App\Repository\MacroRepository;
use App\Service\GravatarManager;
use Gravatar\Gravatar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function index(MacroRepository $macroRepository, GravatarManager $gravatar): Response
    {
        return $this->render('macro/index.html.twig', [
            'macros' => $macroRepository->findAll(),
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }

    /**
     * @Route("/new", name="macro_new", methods={"GET","POST"})
     */
    public function new(Request $request, GravatarManager $gravatar): Response
    {
        $macro = new Macro();
        $form = $this->createForm(MacroType::class, $macro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $macro->addUser($this->getUser());
            $entityManager->persist($macro);
            $entityManager->flush();

            return $this->redirectToRoute('users_index');
        }

        return $this->render('macro/new.html.twig', [
            'macro' => $macro,
            'form' => $form->createView(),
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }

    /**
     * @Route("/{id}", name="macro_show", methods={"GET"})
     */
    public function show(Macro $macro, GravatarManager $gravatar): Response
    {
        return $this->render('macro/show.html.twig', [
            'macro' => $macro,
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="macro_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Macro $macro, GravatarManager $gravatar): Response
    {
        $form = $this->createForm(MacroType::class, $macro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('users_index');
        }

        return $this->render('macro/edit.html.twig', [
            'macro' => $macro,
            'form' => $form->createView(),
            'avatar' => $gravatar->getAvatar($this->getUser()),
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
