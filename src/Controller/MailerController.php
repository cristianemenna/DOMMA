<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;


class MailerController extends AbstractController
{
    /**
     * Envoie de mail à l'utilisateur avec son identifiant et son mot de passe
     * lors de la création de nouveau compte par l'administrateur
     *
     * @Route("/new-user", name="mailer")
     */
    public function newUser(MailerInterface $mailer, $userEmail, $userPassword, $userName, $userUserName)
    {
        $email = (new TemplatedEmail())
            ->from('noreply@domma.fr')
            ->to($userEmail)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Bienvenue chez DOMMA !')
            ->text('Sip')
            ->htmlTemplate('emails/signup.html.twig')
            ->context([
                'password' => $userPassword,
                'user' => $userName,
                'identifiant' => $userUserName,
            ]);

        $mailer->send($email);

        return $this->redirectToRoute('admin');
    }

    /**
     * Envoie de mail à l'utilisateur avec le nouveau mot de passe lors du déblocage
     * de son compte par l'administrateur
     *
     * @Route("/unblocked-user", name="mailer")
     */
    public function unblockedUser(MailerInterface $mailer, $userEmail, $userPassword, $userName)
    {
        $email = (new TemplatedEmail())
            ->from('noreply@domma.fr')
            ->to($userEmail)
            ->subject('Bienvenue chez DOMMA !')
            ->htmlTemplate('emails/unblocked.html.twig')
            ->context([
                'password' => $userPassword,
                'user' => $userName,
            ]);

        $mailer->send($email);

        return $this->redirectToRoute('admin');
    }
}
