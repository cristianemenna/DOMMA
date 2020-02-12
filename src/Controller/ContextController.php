<?php

namespace App\Controller;

use App\Entity\Context;
use App\Form\ContextType;
use App\Repository\ContextRepository;
use App\Service\GravatarHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/context")
 */
class ContextController extends AbstractController
{
    /**
     * @Route("/", name="context_index", methods={"GET"})
     */
    public function index(ContextRepository $contextRepository, Security $security, GravatarHelper $gravatar): Response
    {
        return $this->render('context/index.html.twig', [
            'contexts' => $contextRepository->findAll(),
            'avatar' => $gravatar->getAvatar($security),
        ]);
    }

    /**
     * @Route("/new", name="context_new", methods={"GET","POST"})
     */
    public function new(Request $request, Security $security, GravatarHelper $gravatar): Response
    {
        $context = new Context();
        $form = $this->createForm(ContextType::class, $context);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $context->setCreatedAt(new \DateTime());
            $connectedUser = $security->getUser();
            $context->addUser($connectedUser);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($context);
            $entityManager->flush();

            return $this->redirectToRoute('context_index');
        }

        return $this->render('context/new.html.twig', [
            'context' => $context,
            'form' => $form->createView(),
            'avatar' => $gravatar->getAvatar($security),
        ]);
    }

    /**
     * @Route("/{id}", name="context_show", methods={"GET"})
     */
    public function show(Context $context, Security $security, GravatarHelper $gravatar): Response
    {
        $user = $security->getUser();
        if (!$context->getUsers()->contains($user))
        {
            throw $this->createNotFoundException();
        }

        return $this->render('context/show.html.twig', [
            'context' => $context,
            'avatar' => $gravatar->getAvatar($security),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="context_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Context $context, Security $security, GravatarHelper $gravatar): Response
    {
        $user = $security->getUser();
        if (!$context->getUsers()->contains($user))
        {
            throw $this->createNotFoundException();
        }
        
        $form = $this->createForm(ContextType::class, $context);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('context_index');
        }

        return $this->render('context/edit.html.twig', [
            'avatar' => $gravatar->getAvatar($security),
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
