<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersEditType;
use App\Form\UsersPasswordType;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use App\Service\GravatarManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Gravatar\Gravatar;

/**
 * @Route("/accueil")
 */
class UsersController extends AbstractController
{
    /**
     * @Route("/", name="users_index", methods={"GET"})
     */
    public function index(UsersRepository $usersRepository, Security $security, GravatarManager $gravatar): Response
    {
        $user = $this->getUser();
        return $this->render('users/index.html.twig', [
            'user' => $user,
            'avatar' => $gravatar->getAvatar($user),
            'contextes'=>$user->getContexts(),
        ]);
    }

    /**
     * @Route("/{id}", name="users_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Users $user, UserPasswordEncoderInterface $encoder, GravatarManager $gravatar): Response
    {
        if ($user != $this->getUser())
        {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(UsersEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('users_index');
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
    public function delete(Request $request, Users $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
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
        if ($user != $this->getUser())
        {
            throw $this->createNotFoundException();
        }

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
