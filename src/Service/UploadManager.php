<?php

namespace App\Service;

use App\Entity\Context;
use App\Entity\Import;
use App\Repository\ImportRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;


class UploadManager
{
    private $uploadsDirectory;

    public function __construct($uploadsDirectory,
                                EntityManagerInterface $entityManager,
                                ImportRepository $importRepository,
                                LoadFileManager $loadFileManager)
    {
        $this->uploadsDirectory = $uploadsDirectory;
        $this->entityManager = $entityManager;
        $this->importRepository = $importRepository;
        $this->loadFileManager = $loadFileManager;
    }

    /**
     * Upload des fichiers et association avec leur contexte
     *
     * @param $form
     * @param $context
     */
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

    /**
     * Recupère le dossier où les fichiers sont enregistrés
     *
     * @return mixed
     */
    public function getUploadsDirectory()
    {
        return $this->uploadsDirectory;
    }

    /**
     * Lecture des fichiers et ajout de leur contenu en BDD
     *
     * @param Context $context
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Exception
     */
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

                // Essaie de créer une table en BDD
                try {
                    $this->loadFileManager->createTable($import, $import->getContext(), $sheetColumns);
                } catch (\Exception $e) {
                        $context->removeImport($import);
                        $this->entityManager->remove($import);
                        $this->entityManager->flush();
                    throw new \Exception($e->getMessage());
                    // Dans tous les cas, supprime le fichier du serveur
                } finally {
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                // Essaie d'ajouter les lignes du fichier (contenu) en BDD
                try {
                    $this->loadFileManager->addRows($import, $import->getContext(), $sheetColumns);
                } catch (\Exception $e) {
                    throw new \Exception('Le fichier ' . $import->getfilename() . ' n\'as pas pu être chargé.');
                }

            }
        }
    }
}

