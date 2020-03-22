<?php


namespace App\Service;


use App\Entity\Import;
use Doctrine\ORM\EntityManagerInterface;

class MacroManager
{
    private $entityManager;
    private $loadFileManager;

    public function __construct(EntityManagerInterface $entityManager, LoadFileManager $loadFileManager)
    {
        $this->entityManager = $entityManager;
        $this->loadFileManager = $loadFileManager;
    }

    public function applyMacro(MacroApplyManager $macro, Import $import)
    {
        // Vérifie le type de la macro appliquée et exécute la fonction correspondante
        switch ($macro->getMacro()->getType())
        {
            case 'select':
                // Résultat est un tableau :
                // Premier élément = résultat du select
                // Deuxième élément = colonnes du fichier
                return array($this->select($macro, $import), $this->columnsForSelect($macro, $import));
                break;
            case 'update':
                return $this->update($macro, $import);
                break;
            case 'tiret-par-espace':
                return $this->replaceHyphenBySpace($macro, $import);
                break;
        }
    }

    // Application de macro de type "Select"
    public function select(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        // Recupère le code de la macro
        $macroCode = $macro->getMacro()->getCode();

        // Recupère le nom du contexte pour identifier le nom du schema de l'import
        $schemaName = $dataBase->quoteIdentifier(mb_strtolower($import->getContext()->getTitle()));
        $schemaName = str_replace([' '], '_', $schemaName);
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        // Création de la requête avec le code de la macro
        $requestSQL = 'SELECT ' . $macroCode . ' FROM ' . $schemaName . '.' . $tableName;
        $statement = $dataBase->executeQuery($requestSQL);

        return $statement->fetchAll();
    }

    // Recupère le nom des colonnes du résultat du select
    public function columnsForSelect(MacroApplyManager $macro, Import $import)
    {
        $columns = $this->select($macro, $import)[0];
        $columnsName = [];
        // Duplique en clef/valeur juste la première ligne des résultats de la requête, pour afficher le nom des colonnes
        foreach ($columns as $key => $value)
        {
            $columnsName[$key] = $key;
        }

        return $columnsName;
    }

    // Application de macro qui substitue les tirets par espaces vide
    public function replaceHyphenBySpace(MacroApplyManager $macro, Import $import)
    {
        $dataBase = $this->entityManager->getConnection();
        // Recupère le code de la macro
        $macroCode = $macro->getMacro()->getCode();

        // Recupère le nom du contexte pour identifier le nom du schema de l'import
        $schemaName = $dataBase->quoteIdentifier(mb_strtolower($import->getContext()->getTitle()));
        $schemaName = str_replace([' '], '_', $schemaName);
        // Recupère le nom de la table de l'import
        $tableName = $dataBase->quoteIdentifier('import_'. strval($import->getId()));

        // Création de la requête avec le code de la macro
        $requestSQL = 'UPDATE ' . $schemaName . '.' . $tableName . ' SET ' . $macroCode . ' = REPLACE(' . $macroCode . ", '-', ' ')";
        $statement = $dataBase->executeQuery($requestSQL);

        // Retourne un tableau : premier élément est le contenu de la table, le deuxième sont les colonnes
        return array($this->loadFileManager->showTable($import),
                        $this->loadFileManager->showColumns($import));
    }

}