<?php

namespace App\Controller;

use App\Entity\Macros;
use App\Form\MacrosType;
use App\Repository\MacrosRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/macros")
 */
class MacrosController extends AbstractController
{
    /**
     * @Route("/", name="macros_index", methods={"GET"})
     */
    public function index(MacrosRepository $macrosRepository): Response
    {
        return $this->render('macros/index.html.twig', [
            'macros' => $macrosRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="macros_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $macro = new Macros();
        $form = $this->createForm(MacrosType::class, $macro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($macro);
            $entityManager->flush();

            return $this->redirectToRoute('macros_index');
        }

        return $this->render('macros/new.html.twig', [
            'macro' => $macro,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="macros_show", methods={"GET"})
     */
    public function show(Macros $macro): Response
    {
        return $this->render('macros/show.html.twig', [
            'macro' => $macro,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="macros_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Macros $macro): Response
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
