<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersEditType;
use App\Form\UsersPasswordType;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use App\Service\ContextManager;
use App\Service\MacroManager;
use App\Service\PasswordManager;
use Doctrine\ORM\EntityManagerInterface;
use Gravatar\Gravatar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin_index")
     */
    public function index(UsersRepository $usersRepository, Gravatar $gravatar)
    {
        return $this->render('admin/index.html.twig', [
            'users' => $usersRepository->orderByUsername(),
            'avatar' => $gravatar->avatar($this->getUser()->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
        ]);
    }

    /**
     * @Route("/new", name="admin_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $encoder, Gravatar $gravatar): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $this->forward('App\Controller\MailerController::newUser', [
                'user' => $user,
                ]);

            $user->setPassword(
                $encoder->encodePassword(
                    $user,
                    $user->getPassword()
                )
            );

            try {
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success',
                    'Le nouveau compte a bien été crée. L\'utilisateur recevra un email avec ses identifiants d\'accès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Un problème inconnu est survenu. Veuillez réessayer.');
                return $this->redirectToRoute('admin_new');
            }

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'avatar' => $gravatar->avatar($this->getUser()->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_edit", methods={"GET","POST"})
     */
   public function edit(Request $request, Users $user, UserPasswordEncoderInterface $encoder, Gravatar $gravatar): Response
    {
        $this->denyAccessUnlessGranted('view', $user);

        $form = $this->createForm(UsersEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            try {
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success',
                    'Votre compte a bien été mis à jour');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Un problème inconnu est survenu. Veuillez réessayer.');
                return $this->redirectToRoute('admin_edit');
            }

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'avatar' => $gravatar->avatar($this->getUser()->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_users_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Users $user, ContextManager $contextService, MacroManager $macroManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            // Itère sur le tableau de contextes et de macros de l'utilisateur, s'il est le seul à y avoir accès :
            // supprime la macro/contexte, ainsi que le schema et imports associés
            try {
                $contextService->removeContextsFromUser($user->getContexts());
                $macroManager->removeMacrosFromUser($user->getMacros());
                $entityManager->remove($user);
                $entityManager->flush();
                $this->addFlash('success', 'Le compte a bien été supprimé.');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            } finally {
                return $this->redirectToRoute('admin_index');
            }
        }
    }

    /**
     * @Route("/{id}/changement-mot-de-passe", name="admin_password", methods={"GET","POST"})
     * Permet le changement de mot de passe par un administrateur
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
                return $this->redirectToRoute('admin_edit', ['id' => $user->getId()]);
            }
        }

        return $this->render('users/change_password.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'avatar' => $gravatar->avatar($this->getUser()->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
        ]);
    }

    /**
     * Fonction qui prend en paramètre l'ID de l'utilisateur et vérifie s'il est bloqué (attempts >= 3) ou pas.
     *
     * S'il est bloqué, ses attempts reviennent à 0, un nouveau mot de passe aleatoire est généré
     * et envoyé à l'utilisateur par mail, et le compte est débloqué.
     *
     * S'il est actif, les attempts deviennent 3 et le compte sera bloqué.
     *
     * @Route("/admin/locked/{id}", name="admin_locked", methods={"GET","POST"})
     */

    public function locked(Request $request, Users $user, PasswordManager $passwordHelper, UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager): Response
    {
        if ($user->getAttempts() >= 3){
            $user->resetAttempts();

            $newPassword = $passwordHelper->randomPassword();
            $this->forward('App\Controller\MailerController::unblockedUser', [
                'user' => $user,
                'userRandomPassword' => $newPassword,
            ]);

            $user->setPassword(
                $encoder->encodePassword(
                    $user,
                    $newPassword
                )
            );
            $this->addFlash('success', 'L\'utilisateur recevra un mail avec ses nouveaux identifiants.');
        } else {
            $user->setAttempts(3);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('admin_index');
    }
}
