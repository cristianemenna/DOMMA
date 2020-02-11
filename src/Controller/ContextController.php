<?php

namespace App\Controller;

use App\Entity\Context;
use App\Form\ContextType;
use App\Repository\ContextRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/context")
 */
class ContextController extends AbstractController
{
    /**
     * @Route("/", name="context_index", methods={"GET"})
     */
    public function index(ContextRepository $contextRepository): Response
    {
        return $this->render('context/index.html.twig', [
            'contexts' => $contextRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="context_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $context = new Context();
        $form = $this->createForm(ContextType::class, $context);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($context);
            $entityManager->flush();

            return $this->redirectToRoute('context_index');
        }

        return $this->render('context/new.html.twig', [
            'context' => $context,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="context_show", methods={"GET"})
     */
    public function show(Context $context): Response
    {
        return $this->render('context/show.html.twig', [
            'context' => $context,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="context_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Context $context): Response
    {
        $form = $this->createForm(ContextType::class, $context);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('context_index');
        }

        return $this->render('context/edit.html.twig', [
            'context' => $context,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="context_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Context $context): Response
    {
        if ($this->isCsrfTokenValid('delete'.$context->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($context);
            $entityManager->flush();
        }

        return $this->redirectToRoute('context_index');
    }
}
