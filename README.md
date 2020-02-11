# DOMMA

Application pour le traitement de données de masse.
Projet développé dans le cadre de la formation <b>Web in-pulse</b> de l'Ecole Centrale de Nantes.

Pour faire tourner le projet en local :

1. `$ git clone https://github.com/cristianemenna/DOMMA.git`

2. Installer les packages : `composer install`

3. Créer un fichier .env.local et configurer la variable DATABASE_URL.

4. Créer la base de données `$ symfony console doctrine:database:create`

5. Jouer les migrations `$ symfony console doctrine:migrations:migrate`

5. Activer le serveur `$ symfony server:start`

7. Aller sur http://127.0.0.1:8000/


