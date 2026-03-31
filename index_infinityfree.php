<?php
/**
 * ================================================================================
 * INDEX.PHP OPTIMISÉ POUR INFINITYFREE
 * ================================================================================
 *
 * STRUCTURE:
 * htdocs/
 *   ├── index.php          (CE FICHIER)
 *   └── SRM/               (Dossier Laravel complet)
 *
 * À FAIRE AVANT D'UPLOADER:
 * 1. Placez ce fichier à la racine de votre htdocs (à côté du dossier SRM)
 * 2. Uploadez le dossier SRM complet
 * 3. Configurez le fichier .env dans SRM/
 * 4. CHMOD 755 le dossier SRM/storage/
 * 5. Uploadez vendor/ (si composer n'est pas dispo sur le serveur)
 *
 * ================================================================================
 */

// ============================================================================
// 1. DÉTERMINER LE CHEMIN DE BASE
// ============================================================================

$srmPath = __DIR__ . '/SRM';

// Vérifier que le dossier SRM existe
if (!is_dir($srmPath)) {
    die('❌ ERREUR 500: Le dossier "SRM" n\'a pas été trouvé à la racine de htdocs. '
        . 'Vérifiez que la structure est: htdocs/index.php + htdocs/SRM/');
}

$basePath = realpath($srmPath);

// ============================================================================
// 2. CONFIGURATION DE RAPPORT D'ERREURS
// ============================================================================

// En production: ne pas afficher les erreurs directement
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Créer le fichier de log de PHP s'il n'existe pas
$phpErrorLog = $basePath . '/storage/logs/php_errors.log';
@file_put_contents($phpErrorLog, '[' . date('Y-m-d H:i:s') . '] Démarrage du serveur' . PHP_EOL, FILE_APPEND);

// ============================================================================
// 3. CRÉATION DES DOSSIERS CRITIQUES
// ============================================================================

$requiredDirs = [
    'storage',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
];

foreach ($requiredDirs as $dir) {
    $dirPath = $basePath . '/' . $dir;

    if (!is_dir($dirPath)) {
        if (!@mkdir($dirPath, 0755, true)) {
            error_log("Impossible de créer le dossier: $dirPath");
        }
    }

    // Vérifier et corriger les permissions
    if (!is_writable($dirPath)) {
        @chmod($dirPath, 0755);
    }
}

// ============================================================================
// 4. DÉFINIR LES CONSTANTES
// ============================================================================

if (!defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

// ============================================================================
// 5. VÉRIFIER ET CHARGER L'AUTOLOADER COMPOSER
// ============================================================================

$autoloadPath = $basePath . '/vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    die('❌ ERREUR 500: Le fichier vendor/autoload.php n\'existe pas. '
        . 'À FAIRE: '
        . '1. En local: lancez "composer install" dans le dossier SRM '
        . '2. Uploadez le dossier vendor/ généré vers le serveur. '
        . '3. Ou sur InfinityFree: installez composer et lancez "composer install"');
}

// Charger Composer
require $autoloadPath;

// ============================================================================
// 6. DÉMARRER L'APPLICATION LARAVEL
// ============================================================================

try {
    // Vérifier que bootstrap/app.php existe
    $bootstrapPath = $basePath . '/bootstrap/app.php';
    if (!file_exists($bootstrapPath)) {
        throw new Exception('bootstrap/app.php n\'existe pas');
    }

    // Charger l'application Laravel
    $app = require_once $bootstrapPath;

    // ========================================================================
    // 7. TRAITER LA REQUÊTE HTTP
    // ========================================================================

    /**
     * @var \Illuminate\Foundation\Application $app
     */
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

    // Capturer la requête HTTP
    $request = \Illuminate\Http\Request::capture();

    // Traiter la requête
    $response = $kernel->handle($request);

    // Envoyer la réponse
    $response->send();

    // Terminer le kernel
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {

    // ========================================================================
    // 8. GESTION DES EXCEPTIONS
    // ========================================================================

    // Sauvegarder l'exception dans les logs
    $errorLog = $basePath . '/storage/logs/exceptions.log';
    $errorMessage = '[' . date('Y-m-d H:i:s') . '] '
        . get_class($e) . ': ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL
        . 'Stack Trace:' . PHP_EOL . $e->getTraceAsString() . PHP_EOL;

    @file_put_contents($errorLog, $errorMessage, FILE_APPEND);

    // Déterminer le mode debug
    $appDebug = file_exists($basePath . '/.env')
        ? preg_match('/^APP_DEBUG=true/m', file_get_contents($basePath . '/.env'))
        : false;

    // Afficher l'erreur appropriée
    http_response_code(500);

    if ($appDebug) {
        // Mode debug: afficher les détails
        echo '<!DOCTYPE html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<title>Laravel Error - Debug Mode</title>';
        echo '<style>';
        echo 'body { font-family: "Segoe UI", Tahoma, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }';
        echo '.container { max-width: 900px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }';
        echo 'h1 { color: #d32f2f; border-bottom: 3px solid #d32f2f; padding-bottom: 10px; }';
        echo 'h2 { color: #1976d2; margin-top: 30px; }';
        echo '.error-info { background: #fff3cd; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; border-radius: 4px; }';
        echo '.error-file { background: #f4f4f4; border-left: 4px solid #d32f2f; padding: 15px; margin: 20px 0; font-family: monospace; }';
        echo 'pre { overflow-x: auto; background: #282c34; color: #abb2bf; padding: 15px; border-radius: 4px; }';
        echo 'code { font-family: monospace; }';
        echo '.warning { color: #ff9800; font-weight: bold; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<div class="container">';
        echo '<h1>❌ Erreur Laravel (Mode Debug)</h1>';

        echo '<div class="error-info">';
        echo '<p><strong>Type d\'erreur:</strong> ' . htmlspecialchars(get_class($e)) . '</p>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';

        echo '<div class="error-file">';
        echo '<p><strong>Fichier:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p><strong>Ligne:</strong> ' . $e->getLine() . '</p>';
        echo '</div>';

        echo '<h2>Stack Trace</h2>';
        echo '<pre><code>' . htmlspecialchars($e->getTraceAsString()) . '</code></pre>';

        echo '<h2>Informations de Débogage</h2>';
        echo '<p>';
        echo '• Vérifiez que le dossier <code>SRM/storage/</code> est writable<br>';
        echo '• Vérifiez que <code>SRM/vendor/</code> existe et est complet<br>';
        echo '• Vérifiez que <code>SRM/.env</code> est correctement configuré<br>';
        echo '• Consultez <code>SRM/storage/logs/exceptions.log</code> pour plus d\'info<br>';
        echo '</p>';

        echo '</div>';
        echo '</body>';
        echo '</html>';

    } else {
        // Mode production: message simple
        echo '<!DOCTYPE html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<title>500 - Erreur Serveur</title>';
        echo '<style>';
        echo 'body { font-family: sans-serif; margin: 0; padding: 50px; background: #f5f5f5; text-align: center; }';
        echo '.container { background: white; padding: 40px; border-radius: 8px; max-width: 600px; margin: 50px auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }';
        echo 'h1 { color: #d32f2f; margin-top: 0; }';
        echo 'p { color: #666; line-height: 1.6; }';
        echo '.note { background: #f0f0f0; padding: 15px; border-radius: 4px; margin-top: 20px; font-size: 0.9em; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<div class="container">';
        echo '<h1>❌ 500 - Erreur Serveur</h1>';
        echo '<p>Une erreur s\'est produite lors du traitement de votre demande.</p>';
        echo '<p>Veuillez réessayer dans quelques instants.</p>';
        echo '<div class="note">Si le problème persiste, veuillez contacter l\'administrateur.</div>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
}
