<?php

namespace App\Controller;

use App\Entity\Context;
use App\Entity\Import;
use App\Form\ContextType;
use App\Form\ImportType;
use App\Repository\ContextRepository;
use App\Service\ContexteHelper;
use App\Service\GravatarManager;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(ContextRepository $contextRepository, Security $security, GravatarManager $gravatar): Response
    {
        return $this->render('context/index.html.twig', [
            'contexts' => $contextRepository->findAll(),
            'avatar' => $gravatar->getAvatar($security),
        ]);
    }

    /**
     * @Route("/new", name="context_new", methods={"GET","POST"})
     */
    public function new(Request $request, Security $security, GravatarManager $gravatar, ContextRepository $contextRepository): Response
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
            $contextRepository->createSchema($context->getTitle());

            return $this->redirectToRoute('users_index');
        }

        return $this->render('context/new.html.twig', [
            'context' => $context,
            'form' => $form->createView(),
            'avatar' => $gravatar->getAvatar($security),
        ]);
    }

    /**
     * @Route("/{id}", name="context_show", methods={"GET", "POST"})
     */
    public function show(Context $context, Security $security, GravatarManager $gravatar, Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        // Récupere l'utilisateur actif
        $user = $security->getUser();

        // Si l'utilisateur actif n'as pas droit d'accès au contexte, on affiche un 'Not found'
        if (!$context->getUsers()->contains($user))
        {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadManager->uploadFile($form, $context);

            return $this->redirectToRoute('users_index');
        }

        return $this->render('context/show.html.twig', [
            'context' => $context,
            'imports' => $context->getImports(),
            'avatar' => $gravatar->getAvatar($security),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="context_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Context $context, Security $security, GravatarManager $gravatar): Response
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
