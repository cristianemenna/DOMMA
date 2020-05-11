<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersEditType;
use App\Form\UsersPasswordType;
use App\Repository\UsersRepository;
use App\Service\ContextManager;
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
    public function edit(Request $request, Users $user, UserPasswordEncoderInterface $encoder, Gravatar $gravatar): Response
    {
        $this->denyAccessUnlessGranted('view', $user);

        $form = $this->createForm(UsersEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Vos modifications ont bien été enregistrées.');

            return $this->redirectToRoute('context_index');
        }

        return $this->render('users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'avatar' => $gravatar->avatar($user->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
        ]);
    }

    /**
     * @Route("/{id}", name="users_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Users $user, ContextManager $contextService): Response
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
    public function changePassword(Request $request, Users $user, UserPasswordEncoderInterface $encoder, Gravatar $gravatar)
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

            try {
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Votre mot de passe a bien été mis à jour.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Un problème inconnu est survenu. Veuillez réessayer.');
            } finally {
                return $this->redirectToRoute('users_edit', ['id' => $user->getId()]);
            }

        }

        return $this->render('users/change_password.html.twig', [
            'avatar' => $gravatar->avatar($user->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
            'user' => $this->getUser(),
            'form' => $form->createView(),
        ]);
    }
}
