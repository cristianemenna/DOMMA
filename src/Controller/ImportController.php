<?php

namespace App\Controller;

use App\Entity\Import;
use App\Service\GravatarManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/import")
 */
class ImportController extends AbstractController
{
    /**
     * @Route("/", name="import")
     */
    public function index()
    {
        return $this->render('import/index.html.twig', [
            'controller_name' => 'ImportController',
        ]);
    }

    /**
     * @Route("/{id}", name="import_show", methods={"GET", "POST"})
     */
    public function show(Import $import, Security $security, GravatarManager $gravatar): Response
    {
        return $this->render('import/show.html.twig', [
            'avatar' => $gravatar->getAvatar($security),
        ]);
    }
}
