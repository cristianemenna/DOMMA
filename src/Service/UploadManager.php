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
                // Recupère le nom du fichier téléchargé et le modifie pour générer un nom unique
                $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $uniqueFileName = $originalFileName . '-' . uniqid() . '.' . $file->guessExtension();
                $originalFileName = $originalFileName . '.' . $file->guessExtension();

                // Sauvegarde le fichier dans le dossier choisi pour les uploads
                $file->move(
                    $this->getUploadsDirectory(),
                    $uniqueFileName
                );

                $import = new Import();
                // L'objet porte le nom unique du fichier (modifié)
                $import->setFilePath($uniqueFileName);
                // Et aussi le nom d'origine pour l'affichage
                $import->setFileName($originalFileName);
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

        foreach ($imports as $import) {
            // Réalise la lecture seulement pour les fichiers en attente.
            if ($import->getStatus() === 'En attente') {
                $fileName = $import->getFilePath();
                $filePath = $this->getUploadsDirectory() . '/' . $fileName;
                // Identifie l'extension du fichier
                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filePath);
                // Crée le lecteur adapté selon extension
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                // Lit le tout en format texte
                $reader->setReadDataOnly(true);
                $spreadSheet = $reader->load($filePath);

                $sheetColumns = $spreadSheet->getSheet(0)->getRowIterator();

                // Essai de créer une table en BDD et d'ajouter le contenu du fichier
                try {
                    $this->loadFileManager->createTable($import, $import->getContext()->getTitle(), $sheetColumns);
                    $this->loadFileManager->addRows($import, $import->getContext()->getTitle(), $sheetColumns);
                // En cas d'erreur lors de la création de la table :
                // supprime le fichier du dossier /var/uploads et l'import correspondant en BDD
                } catch (\Exception $e) {
                    if ($e->getMessage() === 'La table ne peut pas être créé') {
                        $context->removeImport($import);
                        $this->entityManager->remove($import);
                        $this->entityManager->flush();
                        // Supprime le fichier du serveur
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                    throw new \Exception();
                }

            }
        }
    }
}
