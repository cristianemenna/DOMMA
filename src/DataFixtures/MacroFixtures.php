<?php

namespace App\DataFixtures;

use App\Entity\Macro;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class MacroFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // Exemple de remplacement de caractères
        $macroOne = new Macro();
        $macroOne->setTitle('Remplacer les "-" par "/" sur "Matricule"');
        $macroOne->setDescription('Remplace tous les caractères "-" par des "/" sur la colonne Matricule.');
        $code = '"Matricule" = REPLACE("Matricule", ' . "'-'" . ', ' . "'/'" . ' )';
        $macroOne->setCode($code);
        $macroOne->setType('update');

        // Exemple de recherche de doublon sur deux colonnes
        $macroTwo = new Macro();
        $macroTwo->setTitle('Recherche des doublons sur Matricule 2019 et Matricule 2020');
        $macroTwo->setDescription('Affiche le nombre d\'ocurrences identiques sur les deux colonnes.');
        $macroTwo->setCode('count("Matricule 2019") over (partition by "Matricule 2019") as doublons_2019, count("Matricule 2020") over (partition by "Matricule 2020") as doublons_2020');
        $macroTwo->setType('select-add-columns');

        // Exemple de filtre sur lignes avec doublons
        $macroThree = new Macro();
        $macroThree->setTitle('Supprime les lignes avec doublon sur Matricule 2019');
        $macroThree->setDescription('Affiche seulement les lignes qui ne contienent pas des doublons pour "Matricule 2019",');
        $macroThree->setCode('doublons_2019::INT > 1');
        $macroThree->setType('delete');

        // Exemple de création de clé unique
        $macroFour = new Macro();
        $macroFour->setTitle('Création de clé d\'authentication unique');
        $macroFour->setDescription('Création de clé unique à partir de la concaténation de champs : 3 premiers caractères de "Nom" + "Prénom" + "Entité".');
        $macroFour->setCode('SUBSTR("Nom", 1, 3) || SUBSTR("Prénom", 1, 3) || SUBSTR("Entité", 1, 3) as cle_authentication');
        $macroFour->setType('select-add-columns');

        // Exemple d'affichage des champs selon critère
        $macroFive = new Macro();
        $macroFive->setTitle('Affiche seulement les 3 premiers caractères de la colonne "Entité"');
        $macroFive->setDescription('Filtre sur la colonne "Entité" pour afficher seulement ses 3 premiers caractères.');
        $macroFive->setCode('"Entité" = SUBSTR("Entité", 1, 3) WHERE LENGTH("Entité") > 3');
        $macroFive->setType('update');

        // Exemple d'update des valeurs d'une ligne
        $macroSix = new Macro();
        $macroSix->setTitle('Marque les lignes avec doublon sur "Matricule 2020"');
        $macroSix->setDescription('Change toutes les valeurs de la colonne "Matricule 2020" à "À Révoir" si il y a des doublons.');
        $code = '"Matricule 2020" = ' . "'À revoir'" . ' WHERE doublons_2019::INT > 1';
        $macroSix->setCode($code);
        $macroSix->setType('update');

        // Exemple de séléction de colonnes
        $macroSeven = new Macro();
        $macroSeven->setTitle('Affiche seulement les colonnes principales.');
        $macroSeven->setDescription('Affiche seulement le contenu des colonnes "Nom", "Prénom", "Email", "Code ONE" et "Entité".');
        $macroSeven->setCode('"Nom", "Prénom", "Email", "Code ONE", "Entité"');
        $macroSeven->setType('select-columns');

        // Exemple de tri de données selon une colonne
        $macroEight = new Macro();
        $macroEight->setTitle('Tri selon l\'ordre décroissant de "Nom".');
        $macroEight->setDescription('Trie les données selon l\'ordre décroissant de la colonne "Nom".');
        $macroEight->setCode('"Nom" DESC');
        $macroEight->setType('tri');

        $user = $this->getReference('user');

        $user->addMacro($macroOne);
        $user->addMacro($macroTwo);
        $user->addMacro($macroThree);
        $user->addMacro($macroFour);
        $user->addMacro($macroFive);
        $user->addMacro($macroSix);
        $user->addMacro($macroSeven);
        $user->addMacro($macroEight);

        $manager->persist($macroOne);
        $manager->persist($macroTwo);
        $manager->persist($macroThree);
        $manager->persist($macroFour);
        $manager->persist($macroFive);
        $manager->persist($macroSix);
        $manager->persist($macroSeven);
        $manager->persist($macroEight);
        $manager->persist($user);
        $manager->flush();
    }

    public function getOrder()
    {
        return 3;
    }

}
