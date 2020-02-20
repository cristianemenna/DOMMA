<?php

namespace App\Service;

use App\Entity\Import;
use Doctrine\ORM\EntityManagerInterface;

class UploadManager
{
    private $uploadsDirectory;

    public function __construct($uploadsDirectory, EntityManagerInterface $entityManager)
    {
        $this->uploadsDirectory = $uploadsDirectory;
        $this->entityManager = $entityManager;
    }

    public function uploadFile($form, $context)
    {
        $importedFile = $form->get('file')->getData();

        if ($importedFile) {
            foreach ($importedFile as $file) {
                /** @var UploadedFile $file */
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $file->guessExtension();

                $file->move(
                    $this->getUploadsDirectory(),
                    $newFilename
                );

                $import = new Import();
                $import->setFile($newFilename);
                $import->setContext($context);
                $this->entityManager->persist($import);
                $this->entityManager->flush();
            }
        }
    }

    public function getUploadsDirectory()
    {
        return $this->uploadsDirectory;
    }
}
