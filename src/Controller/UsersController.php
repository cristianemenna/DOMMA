<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersEditType;
use App\Form\UsersPasswordType;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use App\Service\ContextService;
use App\Service\GravatarManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Gravatar\Gravatar;

/**
 * @Route("/utilisateur")
 */
class UsersController extends AbstractController
{
    /**
     * @Route("/{id}", name="users_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Users $user, UserPasswordEncoderInterface $encoder, GravatarManager $gravatar): Response
    {
        $this->denyAccessUnlessGranted('view', $user);

        $form = $this->createForm(UsersEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('context_index');
        }

        return $this->render('users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }

    /**
     * @Route("/{id}", name="users_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Users $user, ContextService $contextService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            // Itère sur le tableau de contextes de l'utilisateur, s'il est le seul à avoir accès à un contexte :
            // supprime le contexte, ainsi que le schema et imports associés
            $contextService->removeContextsFromUser($user->getContexts());
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/{id}/changement-mot-de-passe", name="users_password", methods={"GET","POST"})
     * Permet le changement de mot de passe par un utilisateur
     */
    public function changePassword(Request $request, Users $user, UserPasswordEncoderInterface $encoder, GravatarManager $gravatar)
    {
        $this->denyAccessUnlessGranted('view', $user);

        $form = $this->createForm(UsersPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $user->setPassword(
                $encoder->encodePassword(
                    $user,
                    $user->getPassword()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('users_edit', ['id' => $user->getId()]);
        }

        return $this->render('users/change_password.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }
}
