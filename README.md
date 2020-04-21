# DOMMA

Application pour le traitement de données de masse.
Projet développé dans le cadre de la formation <b>Web in-pulse</b> de l'Ecole Centrale de Nantes.

### Pour essayer le projet en local :

1. `$ git clone https://github.com/cristianemenna/DOMMA.git`

2. Installer les packages : `composer install`

3. Copier le contenu du fichier .env vers un nouveau fichier ".env.local" et configurer la variable DATABASE_URL.

4. Créer la base de données `$ symfony console doctrine:database:create`

5. Jouer les migrations `$ symfony console doctrine:migrations:migrate`

6. Lire les fixtures : `$ symfony console doctrine:fixtures:load` 

7. Activer le serveur `$ symfony server:start`

8. Aller sur http://127.0.0.1:8000/

### Connexion à l'application

Vous pouvez vous connecter à l'application en tant qu'administrateur en utilisant les identifiants suivants :

- Identifiants : *admin*
- Mot de passe : *test*

Et en tant qu'utilisateur :

- Identifiants : *user1* | *user2*
- Mot de passe : *test*



