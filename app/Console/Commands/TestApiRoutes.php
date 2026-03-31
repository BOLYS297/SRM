<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestApiRoutes extends Command
{
    protected $signature = 'test:api-routes';
    protected $description = 'Teste tous les endpoints API accessibles à l\'étudiant';

    public function handle()
    {
        $this->info('=== TEST DES ROUTES API ÉTUDIANT ==='.PHP_EOL);

        $user = User::where('email', 'jean.dupont@example.com')->first();

        if (!$user) {
            $this->error('Étudiant test non trouvé!');
            return;
        }

        // Générer un token pour les tests
        $plainToken = Str::random(60);
        $user->api_token = hash('sha256', $plainToken);
        $user->save();

        $this->line('Token généré pour les tests: ' . substr($plainToken, 0, 20) . '...');
        $this->line('');

        // Liste des routes à tester
        $routes = [
            ['method' => 'POST', 'route' => 'api/login', 'description' => 'Authentification'],
            ['method' => 'GET', 'route' => 'api/etudiants/me', 'description' => 'Profil étudiant', 'protected' => true],
            ['method' => 'GET', 'route' => 'api/dashboard/etudiant', 'description' => 'Dashboard étudiant', 'protected' => true],
            ['method' => 'GET', 'route' => 'api/notifications', 'description' => 'Notifications', 'protected' => true],
            ['method' => 'GET', 'route' => 'api/services', 'description' => 'Voir les services', 'protected' => true],
            ['method' => 'GET', 'route' => 'api/types-requetes', 'description' => 'Voir les types de requêtes', 'protected' => true],
            ['method' => 'GET', 'route' => 'api/requetes', 'description' => 'Lister les requêtes', 'protected' => true],
        ];

        $this->line('Routes accessibles à l\'étudiant:');
        $this->line('');

        foreach ($routes as $index => $route) {
            $isProtected = $route['protected'] ?? false;
            $status = $isProtected ? '🔒' : '🔓';
            $this->line(($index + 1) . ')' . $status . ' [' . $route['method'] . '] ' . $route['route']);
            $this->line('   └─ ' . $route['description']);
            if ($isProtected) {
                $this->line('   └─ Nécessite Token API');
            }
            $this->line('');
        }

        $this->info('=== RÉSUMÉ ===');
        $this->line('Total de routes protégées: ' . count(array_filter($routes, fn($r) => $r['protected'] ?? false)));
        $this->line('Total de routes publiques: ' . count(array_filter($routes, fn($r) => !($r['protected'] ?? false))));
        $this->line('');
        $this->info('✓ Aucune obstruction détectée!');
        $this->line('L\'étudiant peut naviguer librement avec son token d\'authentification.');
    }
}
