<?php

namespace App\Service;

use App\Entity\Context;
use App\Entity\Import;
use App\Repository\ImportRepository;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\ImportDoctrineCommand;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;


class UploadManager
{
    private $uploadsDirectory;

    public function __construct($uploadsDirectory, EntityManagerInterface $entityManager, ImportRepository $importRepository, LoadFileManager $loadFileManager)
    {
        $this->uploadsDirectory = $uploadsDirectory;
        $this->entityManager = $entityManager;
        $this->importRepository = $importRepository;
        $this->loadFileManager = $loadFileManager;
    }

    // Upload des fichiers et liaison avec leur contexte
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

    // Recupère le dossier où les fichiers sont enregistrés
    public function getUploadsDirectory()
    {
        return $this->uploadsDirectory;
    }

    // Lecture des fichiers
    public function readFile(Context $context)
    {
        $imports = $context->getImports();

        foreach ($imports as $import)
        {
            // Réalise la lecture seulement pour les fichiers en attente.
            if ($import->getStatus() === 'En attente')
            {
                $fileName = $import->getFile();
                $filePath = $this->getUploadsDirectory() . '/' . $fileName;
                // Identifie l'extension du fichier
                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filePath);
                // Crée le lecteur adapté selon extension
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                // Lit le tout en format texte
                $reader->setReadDataOnly(true);
                $spreadSheet = $reader->load($filePath);

                $sheetColumns = $spreadSheet->getSheet(0)->getRowIterator();

                $this->loadFileManager->createTable($import->getId(), $import->getContext()->getTitle(), $sheetColumns);
                $this->loadFileManager->addRows($import->getId(), $import->getContext()->getTitle(), $sheetColumns);
            }
        }
    }
}
