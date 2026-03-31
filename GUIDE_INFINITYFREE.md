# 🚀 Guide d'Hébergement Laravel sur InfinityFree

## ❌ Problème Détecté

Votre fichier `index.php` a plusieurs problèmes:
1. Le chemin vers bootstrap/app.php n'est pas correct
2. Pas de gestion d'erreurs appropriée
3. La méthode `$kernel->handleRequest()` est obsolète

---

## ✅ Solution: Fichier index.php Optimisé

Remplacez votre `index.php` à la racine de **htdocs** par:

```php
<?php
/**
 * Point d'entrée Laravel pour hébergement mutuel
 * Structure: htdocs/index.php  +  htdocs/SRM/
 */

// Configuration des chemins
$basePath = realpath(__DIR__ . '/SRM');

// Vérifier que le dossier SRM existe
if (!$basePath || !is_dir($basePath)) {
    die('Erreur 500: Le dossier SRM n\'existe pas.');
}

// Configuration d'erreurs
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Créer les dossiers critiques s'ils n'existent pas
$dirs = ['storage', 'storage/logs', 'storage/framework', 'bootstrap/cache'];
foreach ($dirs as $dir) {
    $path = $basePath . '/' . $dir;
    if (!is_dir($path)) @mkdir($path, 0755, true);
    @chmod($path, 0755);
}

// Constante de timing
define('LARAVEL_START', microtime(true));

// Loader Composer
require $basePath . '/vendor/autoload.php';

try {
    // Bootstrap Laravel
    $app = require_once $basePath . '/bootstrap/app.php';
    
    // Gestion de requête
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    
    $response->send();
    $kernel->terminate($request, $response);
    
} catch (Throwable $e) {
    // Sauvegarder l'erreur
    @file_put_contents(
        $basePath . '/storage/logs/exceptions.log',
        date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . PHP_EOL,
        FILE_APPEND
    );
    
    // Afficher erreur
    http_response_code(500);
    echo "Erreur 500 - Erreur serveur. Vérifiez storage/logs/exceptions.log";
    
    if (getenv('APP_DEBUG') === 'true') {
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
}
```

---

## 📋 Configuration du Fichier .env

Modifiez `SRM/.env`:

```env
# Mode
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:H9vbmqIyY1YIlrxNoHDrf5BkxsHN3Fe4hmt+zbMNrqA=

# URL IMPORTANTE (à adapter selon votre domaine)
APP_URL=https://votredomaine.infinityfree.com

# Cache
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

# Base de données par défaut
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# LOG
LOG_CHANNEL=single
LOG_LEVEL=error
```

---

## 📁 Structure Attendue sur InfinityFree

```
htdocs/
├── index.php          ✅ Le nouveau fichier optimisé
├── SRM/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/        ⚠️ NE METTEZ PAS CE DOSSIER EN LIGNE
│   ├── resources/
│   ├── routes/
│   ├── storage/       ✅ DOIT EXISTER et être writable
│   ├── vendor/        ✅ DOIT EXISTER
│   ├── .env           ✅ CONFIGURÉ CORRECTEMENT
│   ├── artisan
│   ├── composer.json
│   └── ...autres fichiers
```

---

## 🔑 Points Critiques

### 1️⃣ Le dossier `vendor`
**DOIT** être uploadé. Si absent:
```bash
# En local, lancez:
composer install
```
Puis uploadez le dossier `vendor/` généré.

### 2️⃣ Permissions d'écriture
Le dossier `SRM/storage/` **DOIT** être writable:
- Via FTP: CHMOD 755 ou 777
- Via cPanel File Manager: Make Writable

### 3️⃣ Base de données
- Créez une BD MySQL dans cPanel
- Configurez `DB_*` dans `.env`
- Lancez les migrations (si possible via une commande SSH ou un script)

### 4️⃣ Variable APP_KEY
Ne changez PAS la clé APP_KEY après avoir créé des sessions!

---

## 🐛 Dépannage Erreur 500

### Vérifiez les logs:
```
SRM/storage/logs/exceptions.log
SRM/storage/logs/laravel.log
```

### Vérifications à faire:
```
✓ PHP version ≥ 7.4
✓ Extensions requises: openssl, pdo, mbstring, xml, json
✓ Dossier storage writable
✓ vendor/autoload.php existe
✓ bootstrap/app.php accessible
✓ .env configuré correctement
✓ APP_KEY correctement définie
```

### Si le problème persiste:
1. Ajoutez `APP_DEBUG=true` temporairement dans `.env`
2. Rechargez le site
3. Vérifiez les logs
4. Remettez `APP_DEBUG=false`

---

## 🔧 Configuration InfinityFree Spécifique

InfinityFree a quelques restrictions:

### ⚠️ Limitations Connues:
- Pas d'accès SSH (pas d'artisan en ligne)
- Base de données limitée en taille
- Certaines extensions PHP peuvent être désactivées
- Timeout court sur certaines opérations

### ✅ Solutions:
1. **Migrations**: Lancez `php artisan migrate` **en local** avant d'uploader
2. **Seeders**: Lancez `php artisan db:seed` en local
3. **Cache**: Utilisez `CACHE_DRIVER=file` (plus compatible)
4. **Logs**: Vérifiez régulièrement `storage/logs/`

---

## 📤 Checklist d'Upload

Avant d'uploader:

- [ ] Lancé `composer install` localement
- [ ] Configuré `.env` pour production
- [ ] Lancé `php artisan migrate` localement
- [ ] Lancé `php artisan db:seed` localement (si needed)
- [ ] Copié le nouveau `index.php`
- [ ] Uploadé le dossier `SRM/` complet
- [ ] Uploadé le dossier `vendor/` (ou lancé composer sur le serveur)
- [ ] Défini CHMOD 755 pour `SRM/storage/`
- [ ] Créé la base de données MySQL sur cPanel
- [ ] Configuré les identifiants DA dans le `.env` du serveur

---

## 🚀 Test de Fonctionnement

1. Accédez à: `https://votredomaine.infinityfree.com/`
2. Vous devriez voir la page de connexion Laravel
3. Vérifiez `SRM/storage/logs/` pour les erreurs

---

## 💬 Erreurs Courantes

### "vendor/autoload.php not found"
→ Uploadez le dossier `vendor/` ou lancez `composer install`

### "Permission denied" sur storage
→ CHMOD 755 ou 777 le dossier `storage/`

### "Class 'X' not found"
→ Vérifiez que `vendor/autoload.php` est uploadé et complet

### "SQLSTATE[HY000]"
→ Vérifiez les identifiants BD dans `.env`

### "Storage path does not exist"
→ Créez manuellement `storage/logs/` et `storage/framework/`

---

## 📞 Support Technique

Si le problème persiste:
1. Vérifiez les logs: `SRM/storage/logs/exceptions.log`
2. Activez `APP_DEBUG=true` temporairement
3. Vérifiez les permissions des dossiers
4. Vérifiez que `vendor/` existe et est complet

---

**Statut**: Cette configuration devrait résoudre votre erreur 500! ✅
