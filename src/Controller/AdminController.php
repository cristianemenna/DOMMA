<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersEditType;
use App\Form\UsersPasswordType;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gravatar\Gravatar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin")
     */
    public function index(UsersRepository $usersRepository, Security $security)
    {
        $gravatar = new Gravatar();
        $user = $security->getUser();
        $userMail = $user->getEmail();
        $avatar = $gravatar->avatar($userMail, ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true);

        return $this->render('admin/index.html.twig', [
            'users' => $usersRepository->orderByLastName(),
            'user' => $user,
            'avatar' => $avatar
        ]);
    }

    /**
     * @Route("/new", name="admin_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $encoder, Security $security): Response
    {
        $gravatar = new Gravatar();
        $user = $security->getUser();
        $userMail = $user->getEmail();
        $avatar = $gravatar->avatar($userMail, ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true);

        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
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

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'avatar' => $avatar
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Users $user, UserPasswordEncoderInterface $encoder): Response
    {
        $gravatar = new Gravatar();
        $avatar = $gravatar->avatar($user->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true);

        $form = $this->createForm(UsersEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'avatar' => $avatar
        ]);
    }

    /**
     * @Route("/{id}", name="admin_users_delete", methods={"DELETE"})
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
     * @Route("/{id}/changement-mot-de-passe", name="admin_password", methods={"GET","POST"})
     * Permet le changement de mot de passe par un administrateur
     */
    public function changePassword(Request $request, Users $user, UserPasswordEncoderInterface $encoder, Security $security)
    {
        if ($user != $security->getUser())
        {
            throw $this->createNotFoundException();
        }

        $gravatar = new Gravatar();
        $avatar = $gravatar->avatar($user->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true);

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

            return $this->redirectToRoute('admin_edit', ['id' => $user->getId()]);
        }

        return $this->render('users/change_password.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'avatar' => $avatar
        ]);
    }

    /**
     * @Route("/admin/locked/{id}", name="admin_locked", methods={"GET","POST"})
     * Fonction qui prend en paramètre l'ID de l'utilisateur et vérifie s'il est bloqué (attempts >= 3) ou pas.
     * S'il est bloqué, après cet action ses attempts reviennent à 0 et le compte est débloqué.
     * S'il est actif, les attempts deviennent 3 et le compte sera bloqué.
     */

    public function locked(Request $request, Users $user): Response
    {
        if ($user->getAttempts() >= 3){
            $user->resetAttempts();
        } else {
            $user->setAttempts(3);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('admin');
    }
}
