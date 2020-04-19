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
        // Exemple de filtre sur une colonne
        $macroOne = new Macro();
        $macroOne->setTitle('Filtre sur colonne IDENTIFIANT GLOBAL');
        $macroOne->setDescription('Affiche seulement les lignes du fichiers qui contient un \'Identifant Global Groupe commençant par \'5\'');
        $macroOne->setCode('"identifiant global groupe (igg)" NOT LIKE \'5%\'');
        $macroOne->setType('delete');

        // Exemple de recherche de doublon
        $macroTwo = new Macro();
        $macroTwo->setTitle('Recherche des doublons sur CODE ONE et LANGUE');
        $macroTwo->setDescription('Afficher le nombre d\'ocurrences identiques sur les deux colonnes');
        $macroTwo->setCode('count("code one") over (partition by "code one") as nb_dubble_code_one, count("langue") over (partition by "langue") as nb_dubble_langue');
        $macroTwo->setType('select');

        // Exemple de filtre sur lignes avec doublons
        $macroThree = new Macro();
        $macroThree->setTitle('Filtre les lignes avec doublons sur LANGUE');
        $macroThree->setDescription('Affiche seulement les lignes qui contienent des doublons pour langue');
        $macroThree->setCode('nb_dubble_langue::INT <= 1');
        $macroThree->setType('delete');

        // Exemple de création de clé unique
        $macroFour = new Macro();
        $macroFour->setTitle('Création de clé d\'authentication unique');
        $macroFour->setDescription('Création de clé unique à partir de la concaténation de champs : 3 premiers caractères de l\entité + 4 premiers caractères du igg');
        $macroFour->setCode('SUBSTR("entité actuelle", 1, 3) || SUBSTR("identifiant global groupe (igg)", 1, 4) as cle_authentication');
        $macroFour->setType('select');

        // Exemple d'affichage des champs selon critère
        $macroFive = new Macro();
        $macroFive->setTitle('Affiche seulement les 3 premiers caractères du IGG');
        $macroFive->setDescription('Si le IGG est supérieur à 3 caractères, affiche seulement les trois premiers');
        $macroFive->setCode('"identifiant global groupe (igg)" = SUBSTR("identifiant global groupe (igg)", 1, 3) WHERE LENGTH("identifiant global groupe (igg)") > 3');
        $macroFive->setType('update');

        // Exemple de remplacement de caractères
        $macroSix = new Macro();
        $macroSix->setTitle('Remplace tiret par espace vide sur LANGUE');
        $macroSix->setDescription('Remplace tous les tirets par des espaces vides sur la colonne langue');
        $macroSix->setCode("langue = REPLACE(langue, '-', ' ')");
        $macroSix->setType('update');

        $user = $this->getReference('user');

        $user->addMacro($macroOne);
        $user->addMacro($macroTwo);
        $user->addMacro($macroThree);
        $user->addMacro($macroFour);
        $user->addMacro($macroFive);
        $user->addMacro($macroSix);

        $manager->persist($macroOne);
        $manager->persist($macroTwo);
        $manager->persist($macroThree);
        $manager->persist($macroFour);
        $manager->persist($macroFive);
        $manager->persist($macroSix);
        $manager->persist($user);
        $manager->flush();
    }

    public function getOrder()
    {
        return 3;
    }

}
