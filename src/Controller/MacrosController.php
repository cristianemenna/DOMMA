<?php

namespace App\Controller;

use App\Entity\Macros;
use App\Form\MacrosType;
use App\Repository\MacrosRepository;
use App\Service\GravatarManager;
use Gravatar\Gravatar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/macros")
 */
class MacrosController extends AbstractController
{
    /**
     * @Route("/", name="macros_index", methods={"GET"})
     */
    public function index(MacrosRepository $macrosRepository, GravatarManager $gravatar): Response
    {
        return $this->render('macros/index.html.twig', [
            'macros' => $macrosRepository->findAll(),
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }

    /**
     * @Route("/new", name="macros_new", methods={"GET","POST"})
     */
    public function new(Request $request, GravatarManager $gravatar): Response
    {
        $macro = new Macros();
        $form = $this->createForm(MacrosType::class, $macro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $macro->addUser($this->getUser());
            $entityManager->persist($macro);
            $entityManager->flush();

            return $this->redirectToRoute('macros_index');
        }

        return $this->render('macros/new.html.twig', [
            'macro' => $macro,
            'form' => $form->createView(),
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }

    /**
     * @Route("/{id}", name="macros_show", methods={"GET"})
     */
    public function show(Macros $macro, GravatarManager $gravatar): Response
    {
        return $this->render('macros/show.html.twig', [
            'macro' => $macro,
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="macros_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Macros $macro, GravatarManager $gravatar): Response
    {
        $form = $this->createForm(MacrosType::class, $macro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('macros_index');
        }

        return $this->render('macros/edit.html.twig', [
            'macro' => $macro,
            'form' => $form->createView(),
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }

    /**
     * @Route("/{id}", name="macros_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Macros $macro): Response
    {
        if ($this->isCsrfTokenValid('delete'.$macro->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($macro);
            $entityManager->flush();
        }

        return $this->redirectToRoute('macros_index');
    }
}
