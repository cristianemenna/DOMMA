<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersEditType;
use App\Form\UsersPasswordType;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use App\Service\ContextService;
use App\Service\GravatarManager;
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
    public function index(UsersRepository $usersRepository, GravatarManager $gravatar)
    {
        return $this->render('admin/index.html.twig', [
            'users' => $usersRepository->orderByUsername(),
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }

    /**
     * @Route("/new", name="admin_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $encoder, GravatarManager $gravatar): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $this->forward('App\Controller\MailerController::newUser', [
                'userEmail' => $user->getEmail(),
                'userPassword' => $user->getPassword(),
                'userName' => $user->getFirstName(),
                'userUserName' => $user->getUsername(),
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
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_edit", methods={"GET","POST"})
     */
   public function edit(Request $request, Users $user, UserPasswordEncoderInterface $encoder, GravatarManager $gravatar): Response
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
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_users_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Users $user, ContextService $contextService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            // Itère sur le tableau de contextes de l'utilisateur, s'il est le seul à avoir accès à un contexte :
            // supprime le contexte, ainsi que le schema et imports associés
            try {
                $contextService->removeContextsFromUser($user->getContexts());
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

            try {
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Votre mot de passe a bien été mis à jour.');
                return $this->redirectToRoute('admin_edit', ['id' => $user->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Un problème inconnu est survenu. Veuillez réessayer.');
                return $this->redirectToRoute('admin_password');
            }
        }

        return $this->render('users/change_password.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'avatar' => $gravatar->getAvatar($this->getUser()),
        ]);
    }

    /**
     * @Route("/admin/locked/{id}", name="admin_locked", methods={"GET","POST"})
     * Fonction qui prend en paramètre l'ID de l'utilisateur et vérifie s'il est bloqué (attempts >= 3) ou pas.
     *
     * S'il est bloqué, après cet action ses attempts reviennent à 0, un nouveau mot de passe aleatoire est généré
     * et envoyé à l'utilisateur par mail, et le compte est débloqué.
     *
     * S'il est actif, les attempts deviennent 3 et le compte sera bloqué.
     */

    public function locked(Request $request, Users $user, PasswordManager $passwordHelper, UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager): Response
    {
        if ($user->getAttempts() >= 3){
            $user->resetAttempts();

            $newPassword = $passwordHelper->randomPassword();
            $this->forward('App\Controller\MailerController::unblockedUser', [
                'userEmail' => $user->getEmail(),
                'userPassword' => $newPassword,
                'userName' => $user->getFirstName(),
                'userUserName' => $user->getUsername(),
            ]);

            $user->setPassword(
                $encoder->encodePassword(
                    $user,
                    $newPassword
                )
            );
        } else {
            $user->setAttempts(3);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('admin_index');
    }
}
