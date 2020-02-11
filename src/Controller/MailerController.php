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

        /** @var Symfony\Component\Mailer\SentMessage $sentEmail */
        $sentEmail = $mailer->send($email);

        return $this->redirectToRoute('admin');
    }

    /**
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

        /** @var Symfony\Component\Mailer\SentMessage $sentEmail */
        $sentEmail = $mailer->send($email);

        return $this->redirectToRoute('admin');
    }
}
