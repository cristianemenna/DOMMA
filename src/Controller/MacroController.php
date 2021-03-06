<?php

namespace App\Controller;

use App\Entity\Macro;
use App\Form\MacroType;
use App\Form\ShareMacroType;
use App\Repository\ImportRepository;
use App\Repository\MacroRepository;
use App\Repository\UsersRepository;
use Gravatar\Gravatar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/macro")
 */
class MacroController extends AbstractController
{
    /**
     * @Route("/mes-macros", name="macro_index", methods={"GET"})
     */
    public function index(MacroRepository $macroRepository, Gravatar $gravatar, SessionInterface $session): Response
    {
        // Réinitialise la variable de session Import,
        // pour la gestion des redirections suite à une création de macro
        $session->set('import', null);
        return $this->render('macro/index.html.twig', [
            'macros' => $this->getUser()->getMacros(),
            'avatar' => $gravatar->avatar($this->getUser()->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
        ]);
    }

    /**
     * @Route("/creation-de-macro", name="macro_new", methods={"GET","POST"})
     */
    public function new(Request $request,
                        Gravatar $gravatar,
                        SessionInterface $session,
                        ImportRepository $importRepository): Response
    {
        $macro = new Macro();
        $form = $this->createForm(MacroType::class, $macro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $macro->addUser($this->getUser());

            try {
                $entityManager->persist($macro);
                $entityManager->flush();
                $this->addFlash('success', 'La macro a bien été crée.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Un problème inconnu est survenu. Veuillez réessayer.');
            } finally {
                // Redirection sur la page de l'import s'il y a une variable 'import' stockée en session
                if ($session->get('import')) {
                    $import = $importRepository->find($session->get('import'));
                    $session->set('context', $import->getContext());
                    return $this->redirectToRoute('import_show', ['context' => $import->getContext()->getId(), 'id' => $import->getId()]);
                    // Sinon redirection sur la page index des macros
                } else {
                    return $this->redirectToRoute('macro_index');
                }
            }
        }

        return $this->render('macro/new.html.twig', [
            'macro' => $macro,
            'form' => $form->createView(),
            'avatar' => $gravatar->avatar($this->getUser()->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
        ]);
    }

    /**
     * @Route("/{id}", name="macro_edit", methods={"GET","POST"})
     */
    public function edit(Request $request,
                         Macro $macro,
                         Gravatar $gravatar,
                         SessionInterface $session,
                         ImportRepository $importRepository): Response
    {
        // Si l'utilisateur actif n'as pas droit d'accès à la macro, on affiche page 403'
        $this->denyAccessUnlessGranted('edit', $macro);
        $form = $this->createForm(MacroType::class, $macro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', 'La macro a bien été modifiée.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Un problème inconnu est survenu. Veuillez réessayer.');
            } finally {
                return $this->redirectToRoute('macro_index');
            }
        }

        return $this->render('macro/edit.html.twig', [
            'macro' => $macro,
            'form' => $form->createView(),
            'avatar' => $gravatar->avatar($this->getUser()->getEmail(), ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true),
        ]);
    }

    /**
     * @Route("/{id}", name="macro_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Macro $macro): Response
    {
        if ($this->isCsrfTokenValid('delete'.$macro->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($macro);
            $entityManager->flush();
        }
        return $this->redirectToRoute('macro_index');
    }


    /**
     * Route qui permet l'édition d'un macro en ajax
     * directement sur la page show d'un Import
     *
     * @Route("/{id}/ajax", name="macro_edit_ajax", methods={"GET","POST"})
     */
    public function ajaxEditMacro(Request $request, Macro $macro)
    {
        // Si la requête est en ajax et la méthode GET
        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            // Envoie les informations de la macro pour affichage sur le form d'édition
            $jsonData = [
                'id' => $macro->getId(),
                'title' => $macro->getTitle(),
                'description' => $macro->getDescription(),
                'code' => $macro->getCode(),
                'type' => $macro->getType()
            ];
            return new JsonResponse($jsonData);

        // Si la requête est en ajax et la méthode en POST
        } elseif ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            // Reçoie le contenu modifié en format JSON
            $reponse = $request->getContent();
            $json = json_decode($reponse);

            // Update les informations de la macro
            $macro->setTitle($json->title);
            $macro->setDescription($json->description);
            $macro->setCode($json->code);
            $macro->setType($json->type);
            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse($json);
        }
    }

    /**
     * Partage d'une Macro en AJAX
     *
     * @Route("/{id}/share", name="macro_share", methods={"GET","POST"})
     */
    public function share(Request $request, Macro $macro, UsersRepository $usersRepository)
    {
        $users = $usersRepository->findAll();
        // Supprime l'utilisateur actif et les administrateurs du tableau envoyé à la vue
        foreach ($users as $key => $user) {
            if ($user->getRole() !== 'ROLE_USER' || $user === $this->getUser()) {
                unset($users[$key]);
            }
        }
        $form = $this->createForm(ShareMacroType::class, $macro, ['users' => $users]);

        // Envoi du formulaire de partage de macro en ajax
        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            $template = $this->render('users/share.html.twig', [
                'form' => $form->createView(),
            ])->getContent();
            $json = json_encode($template);

            return new JsonResponse($json);

            // Requête post avec les id's des utilisateurs pour le partage des macros
        } elseif ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $reponse = $request->getContent();
            $json = json_decode($reponse);
            $formArray = [];

            // Ajoute chaque utilisateur aux tableaux d'utilisateurs de cette macro
            foreach ($json as $userId) {
                $user = $usersRepository->find($userId);
                $macro->addUser($user);
                $formArray[] = $user;
            }

            // Si l'utilisateur n'est pas présent dans le tableaux de la requête
            // alors supprime l'utilisateur du tableau des macros
            foreach ($macro->getUsers() as $user) {
                if (!in_array($user, $formArray, true)) {
                    $macro->removeUser($user);
                }
            }
            $macro->addUser($this->getUser());
            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse(['success' => 'OK']);
        }
        return $this->redirectToRoute('macro_index');
    }
}
