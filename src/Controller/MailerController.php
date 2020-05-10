<?php

namespace App\Controller;

use App\Entity\Users;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;


class MailerController extends AbstractController
{
    /**
     * Envoi de mail à l'utilisateur avec son identifiant et son mot de passe
     * lors de la création de nouveau compte par l'administrateur
     *
     * @Route("/new-user", name="mailer")
     */
    public function newUser(MailerInterface $mailer, Users $user)
    {
        if ($user->getRoles() === 'ROLE_USER') {
            $role = 'utilisateur';
        } else {
            $role = 'administrateur';
        }

        $email = (new TemplatedEmail())
            ->from('noreply@domma.fr')
            ->to($user->getEmail())
            ->subject('Bienvenue chez DOMMA !')
            ->htmlTemplate('emails/signup.html.twig')
            ->context([
                'password' => $user->getPassword(),
                'user' => $user->getFirstName(),
                'identifiant' => $user->getUsername(),
                'role' => $role,
            ]);

        $mailer->send($email);

        return $this->redirectToRoute('admin_index');
    }

    /**
     * Envoi de mail à l'utilisateur avec le nouveau mot de passe lors du déblocage
     * de son compte par l'administrateur
     *
     * @Route("/unblocked-user", name="mailer")
     */
    public function unblockedUser(MailerInterface $mailer, Users $user, string $userRandomPassword)
    {
        $email = (new TemplatedEmail())
            ->from('noreply@domma.fr')
            ->to($user->getEmail())
            ->subject('DOMMA - Réinitialisation de mot de passe !')
            ->htmlTemplate('emails/unblocked.html.twig')
            ->context([
                'password' => $userRandomPassword,
                'user' => $user->getFirstName(),
                'identifiant' => $user->getUsername(),
            ]);

        $mailer->send($email);

        return $this->redirectToRoute('admin_index');
    }
}
