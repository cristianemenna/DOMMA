<p align="center">
  <img src="https://user-images.githubusercontent.com/48241779/82093210-fdaa2c80-96fa-11ea-995e-986702113c10.png" alt="DOMMA" width="40%">
</p>


*DOMMA* est une application web pour le traitement de données de masse provenant de fichiers Excel. La plateforme permet le chargement et l'affichage du contenu des fichiers en format .xls, .xlsx et .csv, en facilitant leur manipulation à partir de l'application de macros personnalisées par l'utilisateur. 

Suite aux traitements, il est également possible d'exporter les données vers un nouveau fichier tableur.

## Le projet

L'application web a été développée avec le framework PHP Symfony version 5.0.4 et en utilisant la version 7.2.5 de PHP. Le système de gestion de base de données utilisé est PostgreSQL dans sa version 11.

### Installation en local

#### Requis technique
* [PHP](https://www.php.net/manual/fr/install.php) version 7.2.5 ou supérieur
* [Symfony CLI](https://symfony.com/download)
* [Composer](https://getcomposer.org/download)
* [PostgreSQL](https://www.postgresql.org/download/) version 11 ou supérieur

#### Accès au projet
```
$ git clone https://github.com/cristianemenna/DOMMA.git 
$ cd DOMMA/ 
$ composer install
$ cp .env .env.local
```

Une fois le fichier `DOMMA/.env.local` créé, adapter la variable `DATABASE_URL` avec vos identifiants et un nom pour la base de données à être créé en local.

<br>

> DATABASE_URL=postgresql://**{utilisateur}**:**{mot_de_passe}**@127.0.0.1:5432/**{nom_BDD}**?serverVersion=11&charset=utf8

<br>

DOMMA utilise *SendGrid* pour l'envoi de mails. Si vous voulez tester son fonctionnement en local avec l'envoi de vrais emails, vous devez ajouter la ligne ci-dessous à votre `DOMMA/.env.local` :

<br>

> MAILER_DSN=sendgrid://**{votre_clé}**@default

<br>

Ensuite, pour créer la base de données et exécuter les migrations :

```
$ symfony console doctrine:database:create
$ symfony console doctrine:migrations:migrate
```

Pour accéder aux comptes administrateur et utilisateur créés par défaut, ainsi que pour visualiser les modèles de macro proposés en tant qu'exemple, lire les fixtures :

```
$ symfony console doctrine:fixtures:load
```

Il ne reste plus que lancer le serveur local de Symfony pour exécuter l'application.

```
$ symfony server:start
```

#### Exemples créés par défaut :

* Admin : 

    * Identifiant : admin
    * Mot de passe : test
    

* Utilisateurs :

    * Identifiant : user1 | user2
    * Mot de passe: test

Dans `/DOMMA/public` vous trouverez également un fichier (`demo.xls`) à titre d'exemple. Ce fichier contient des données modèles pour la création des macros de traitement générées par les fixtures.

## Utilisation

En tant qu'utilisateur de DOMMA, vous pouvez créer des contextes de travail personnalisés et partagés avec d'autres utilisateurs. Cela vous permettra d'organiser les fichiers chargés par sujet ou groupes, afin de mieux structurer l'information.
Vous pouvez générer et appliquer vos propres macros pour traiter les données importées et, ensuite, télécharger les modifications en format .xls, xlsx ou .csv. 

Le fonctionnement des macros se base sur le langage de requêtes SQL. La connaissance basique de de ce langage est donc requise pour l'utilisation de la plateforme. 

Pour commencer à utiliser l'application, il est recommandé de lire de son [Manuel d'utilisation](https://github.com/cristianemenna/DOMMA/wiki).

## Bundles

En plus des bibliothèques installées par défaut lors de l'initialisation du projet, DOMMA intègre les bundles listés ci-dessous :
* gravatarphp/gravatar ^2.0 => Affichage des avatars liés à un compte utilisateur
* phpoffice/phpspreadsheet ^1.10  => Bibliothèque permettant la lecture et la création de fichiers en format Excel
* symfony/sendgrid-mailer 5.0 => Envoi des emails
 

## Contributeurs 

DOMMA a été développée en binôme par:

* [Cristiane MENNA](https://github.com/cristianemenna)
* [Thibault GUILLONNEAU](https://github.com/ThibaultG10)

Ce projet a été présenté en tant que travail final dans le cadre de la formation Web in pulse de l'École Centrale de Nantes, en partenariat avec l'entreprise UmanIT.
