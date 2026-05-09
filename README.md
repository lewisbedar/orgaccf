# OrgaCCF Laravel/Vite

Application Laravel/Vite pour organiser les CCF de langues vivantes en lycée professionnel.

## Installation WampServer

1. Placer le projet dans `C:\wamp64\www\orgaccf`.
2. Créer la base MySQL/MariaDB `orgaccf`.
3. Vérifier `.env` :

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=orgaccf
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Lancer :

   ```bash
   composer install
   npm install
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   npm run build
   ```

5. Créer un VirtualHost `orgaccf.local` pointant vers `C:\wamp64\www\orgaccf\public`.
6. Activer `rewrite_module` et vérifier `AllowOverride All`.
7. Se connecter avec `admin / admin123`.

La première version PHP simple est conservée dans `_legacy_plain_php`.

## GitHub

Le projet est prêt à être versionné. Pour synchroniser avec GitHub, indique le dépôt cible sous la forme `owner/repo`, ou demande-moi de créer un nouveau dépôt si tu préfères partir d’un dépôt vierge.
