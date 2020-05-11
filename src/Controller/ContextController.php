<?php

namespace App\Controller;

use App\Entity\Context;
use App\Form\ContextType;
use App\Form\ImportType;
use App\Form\ShareContextType;
use App\Repository\ContextRepository;
use App\Repository\UsersRepository;
use App\Service\ContextManager;
use App\Service\UploadManager;
use Gravatar\Gravatar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/contextes")
 */
class ContextController extends AbstractController
{
    /**
     * @Route("/", name="context_index", methods={"GET"})
     */
    public function index(Gravatar $gravatar, SessionInterface $session): Response
    {
        // Réinitialise la variable de session Context,
        // pour la gestion des redirections suite à une édition de contexte
        $session->set('context', null);
        return $this->render('context/index.html.twig', [
            'user' => $this->getUser(),
            'avatar' => $gravatar->avatar($this->getUser()->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
            'contextes'=> $this->getUser()->getContexts(),
        ]);
    }

    /**
     * @Route("/new", name="context_new", methods={"GET","POST"})
     */
    public function new(Request $request, Gravatar $gravatar, ContextRepository $contextRepository): Response
    {
        $context = new Context();
        $form = $this->createForm(ContextType::class, $context);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $entityManager = $this->getDoctrine()->getManager();

            try {
                $context->addUser($user);
                $entityManager->persist($context);
                $contextRepository->createSchema($context);
                $entityManager->flush();
                $this->addFlash('success', 'Votre contexte de travail a bien été créé !');
            // En cas d'erreur lors de la création du schema
            // affiche le message d'erreur sur la page de création de contexte
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('context_new');
            }
            
            return $this->redirectToRoute('context_index');
        }

        return $this->render('context/new.html.twig', [
            'context' => $context,
            'form' => $form->createView(),
            'avatar' => $gravatar->avatar($this->getUser()->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
        ]);
    }

    /**
     * @Route("/{id}", name="context_show", methods={"GET", "POST"})
     */
    public function show(Context $context,
                         Gravatar $gravatar,
                         Request $request,
                         UploadManager $uploadManager,
                         SessionInterface $session): Response
    {
        // Récupere l'utilisateur actif
        $user = $this->getUser();
        // Si l'utilisateur actif n'as pas droit d'accès au contexte, on affiche page 404
        if (!$user->getContexts()->contains($context)) {
            throw $this->createNotFoundException();
        }

        $session->set('context', $context->getId());

        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Affiche un message de confirmation si le fichier est chargé sans erreur en BDD
            // ou un message en cas d'erreur.
            try {
                $uploadManager->uploadFile($form, $context);
                $uploadManager->readFile($context);
                $this->addFlash('success', 'Le fichier a bien été envoyé.');
            } catch (\Exception $e) {
                $this->addFlash(
                    'error', $e->getMessage());
            }

            return $this->redirectToRoute('context_show', ['id' => $context->getId()]);
        }

        return $this->render('context/show.html.twig', [
            'context' => $context,
            'imports' => $context->getImports(),
            'avatar' => $gravatar->avatar($user->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="context_edit", methods={"GET","POST"})
     */
    public function edit(Request $request,
                         Context $context,
                         Gravatar $gravatar,
                         ContextRepository $contextRepository,
                         SessionInterface $session): Response
    {
        // Récupere l'utilisateur actif
        $user = $this->getUser();
        // Si l'utilisateur actif n'as pas droit d'accès au contexte, on affiche page 404
        if (!$user->getContexts()->contains($context)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ContextType::class, $context);
        // Récupère le nom actuel du contexte de travail
        $contextTitle = $form->getData()->getTitle();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Modifie le nom du schéma en BDD si le nom du contexte de travail a été modifié
            if ($contextTitle !== $context->getTitle()) {
                try {
                    $contextRepository->renameSchema($context, $contextTitle);
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                    return $this->redirectToRoute('context_edit');
                }
            }

            $this->addFlash('success', 'Le contexte a bien été modifié.');
            $this->getDoctrine()->getManager()->flush();

            // Une fois le context modifié :
            // Redirection sur la page du context s'il y a une variable 'context' stockée en session
            if ($session->get('context')) {
                return $this->redirectToRoute('context_show',
                    ['id' => $session->get('context')]);
                // Sinon redirection sur la page d'accueil
            } else {
                return $this->redirectToRoute('context_index');
            }
        }

        return $this->render('context/edit.html.twig', [
            'avatar' => $gravatar->avatar($user->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
            'context' => $context,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="context_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Context $context, ContextManager $contextManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$context->getId(), $request->request->get('_token'))) {
            try {
                $contextManager->removeContext($context);
                $this->addFlash('success', 'Le contexte a bien été supprimé.');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
        return $this->redirectToRoute('context_index');
    }

    /**
     * Partage d'un contexte de travail en AJAX
     *
     * @Route("/{id}/share", name="context_share")
     */
    public function share(Request $request, Context $context, UsersRepository $usersRepository)
    {
        $users = $usersRepository->findAll();
        // Supprime l'utilisateur du tableau du contexte envoyé à la vue
        $userPosition = array_search($this->getUser(), $users, true);
        unset($users[$userPosition]);

        $form = $this->createForm(ShareContextType::class, $context, ['users' => $users]);

        // Envoie du formulaire de partage de contexte en ajax
        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            $template = $this->render('context/share.html.twig', [
                'form' => $form->createView(),
            ])->getContent();
            $json = json_encode($template);

            return new JsonResponse($json);

        // Requête post avec les id's des utilisateurs pour le partage de context
        } elseif ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $reponse = $request->getContent();
            $json = json_decode($reponse);
            $formArray = [];

            // Ajoute chaque utilisateur aux tableaux d'utilisateurs de ce contexte
            foreach ($json as $userId) {
                $user = $usersRepository->find($userId);
                if ($context->addUser($user) === true) {
                    $this->forward('App\Controller\MailerController::shareContext', [
                        'user' => $user,
                        'context' => $context,
                    ]);
                }
                $formArray[] = $user;
            }

            // Si l'utilisateur n'est pas présent dans le tableaux de la requête
            // alors supprime l'utilisateur du tableau du contexte
            foreach ($context->getUsers() as $user) {
                if (!in_array($user, $formArray, true)) {
                    $context->removeUser($user);
                }
            }
            $context->addUser($this->getUser());
            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse(['success' => 'Ok']);
        }
    }
}
