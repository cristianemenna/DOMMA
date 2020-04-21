<?php

namespace App\Controller;

use App\Entity\Context;
use App\Entity\Import;
use App\Form\ContextType;
use App\Form\ImportType;
use App\Repository\ContextRepository;
use App\Repository\ImportRepository;
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
 * @Route("/contexte")
 */
class ContextController extends AbstractController
{
    /**
     * @Route("/", name="context_index", methods={"GET"})
     */
    public function index(ContextRepository $contextRepository, GravatarManager $gravatar): Response
    {
        $user = $this->getUser();
        return $this->render('context/index.html.twig', [
            'user' => $user,
            'avatar' => $gravatar->getAvatar($user),
            'contextes'=>$user->getContexts(),
        ]);
    }

    /**
     * @Route("/new", name="context_new", methods={"GET","POST"})
     */
    public function new(Request $request, GravatarManager $gravatar, ContextRepository $contextRepository): Response
    {
        $context = new Context();
        $form = $this->createForm(ContextType::class, $context);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $context->setCreatedAt(new \DateTime());
            $connectedUser = $this->getUser();
            $context->addUser($connectedUser);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($context);
            $entityManager->flush();
            $contextRepository->createSchema($context->getTitle());

            return $this->redirectToRoute('context_index');
        }

        return $this->render('context/new.html.twig', [
            'context' => $context,
            'form' => $form->createView(),
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }

    /**
     * @Route("/{id}", name="context_show", methods={"GET", "POST"})
     */
    public function show(Context $context, GravatarManager $gravatar, Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager, ImportRepository $importRepository): Response
    {
        // Récupere l'utilisateur actif
        $user = $this->getUser();
        // Si l'utilisateur actif n'as pas droit d'accès au contexte, on affiche page 404
        if (!$user->getContexts()->contains($context))
        {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadManager->uploadFile($form, $context);
            $uploadManager->readFile($context);

            return $this->redirectToRoute('context_show', ['id' => $context->getId()]);
        }

        return $this->render('context/show.html.twig', [
            'context' => $context,
            'imports' => $context->getImports(),
            'avatar' => $gravatar->getAvatar($user),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="context_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Context $context, GravatarManager $gravatar): Response
    {
        // Récupere l'utilisateur actif
        $user = $this->getUser();
        // Si l'utilisateur actif n'as pas droit d'accès au contexte, on affiche page 404
        if (!$user->getContexts()->contains($context))
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
            'avatar' => $gravatar->getAvatar($user),
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
