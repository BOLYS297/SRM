<?php

namespace App\Support;

use App\Models\Service;

class AgentRoleMatrix
{
    public static function resolve(?Service $service): array
    {
        $roleKey = self::resolveRoleKey($service);

        $profiles = [
            'courrier' => [
                'label' => 'Service courrier',
                'title' => 'Espace courrier',
                'description' => 'Enregistre les nouvelles requetes et les transmet au service suivant.',
                'features' => [
                    'dashboard',
                    'suivi_requetes',
                    'process_etapes',
                    'manage_services',
                    'manage_types',
                    'manage_agents',
                    'manage_etudiants',
                ],
                'quick_actions' => [
                    ['label' => 'Requetes a traiter', 'url' => '/agent/dashboard'],
                    ['label' => 'Historique', 'url' => '/agent/historique'],
                    ['label' => 'Gestion des agents', 'url' => '/agent/agents'],
                ],
            ],
            'scolarite' => [
                'label' => 'Scolarite',
                'title' => 'Espace scolarite',
                'description' => 'Finalise les dossiers et rend la decision finale.',
                'features' => [
                    'dashboard',
                    'suivi_requetes',
                    'process_etapes',
                    'decision_finale',
                ],
                'quick_actions' => [
                    ['label' => 'Requetes a traiter', 'url' => '/agent/dashboard'],
                    ['label' => 'Historique', 'url' => '/agent/historique'],
                    ['label' => 'Decisions', 'url' => '/agent/decisions'],
                ],
            ],
            'conseil_orientation' => [
                'label' => 'Conseil orientation',
                'title' => 'Espace conseil orientation',
                'description' => 'Recoit les depots et lance la premiere etape du circuit.',
                'features' => ['dashboard', 'suivi_requetes', 'process_etapes'],
                'quick_actions' => [
                    ['label' => 'Requetes a traiter', 'url' => '/agent/dashboard'],
                    ['label' => 'Historique', 'url' => '/agent/historique'],
                ],
            ],
            'direction' => [
                'label' => 'Direction',
                'title' => 'Espace direction',
                'description' => 'Valide et cote les requetes avant orientation.',
                'features' => ['dashboard', 'suivi_requetes', 'process_etapes'],
                'quick_actions' => [
                    ['label' => 'Requetes a traiter', 'url' => '/agent/dashboard'],
                    ['label' => 'Historique', 'url' => '/agent/historique'],
                ],
            ],
            'da' => [
                'label' => 'Direction adjointe',
                'title' => 'Espace direction adjointe',
                'description' => 'Cote les requetes et les transfere vers les departements concernes.',
                'features' => ['dashboard', 'suivi_requetes', 'process_etapes'],
                'quick_actions' => [
                    ['label' => 'Requetes a traiter', 'url' => '/agent/dashboard'],
                    ['label' => 'Historique', 'url' => '/agent/historique'],
                ],
            ],
            'departement' => [
                'label' => 'Departement',
                'title' => 'Espace departement',
                'description' => 'Examine les dossiers academiques avant correction technique.',
                'features' => ['dashboard', 'suivi_requetes', 'process_etapes'],
                'quick_actions' => [
                    ['label' => 'Requetes a traiter', 'url' => '/agent/dashboard'],
                    ['label' => 'Historique', 'url' => '/agent/historique'],
                ],
            ],
            'cellule_info' => [
                'label' => 'Cellule informatique',
                'title' => 'Espace cellule informatique',
                'description' => 'Applique les corrections et renvoie le dossier a la scolarite.',
                'features' => ['dashboard', 'suivi_requetes', 'process_etapes'],
                'quick_actions' => [
                    ['label' => 'Requetes a traiter', 'url' => '/agent/dashboard'],
                    ['label' => 'Historique', 'url' => '/agent/historique'],
                ],
            ],
            'enseignant' => [
                'label' => 'Enseignant',
                'title' => 'Espace enseignant',
                'description' => 'Traite les requetes pedagogiques transmises par le departement.',
                'features' => ['dashboard', 'suivi_requetes', 'process_etapes'],
                'quick_actions' => [
                    ['label' => 'Requetes a traiter', 'url' => '/agent/dashboard'],
                    ['label' => 'Historique', 'url' => '/agent/historique'],
                ],
            ],
            'autre' => [
                'label' => 'Service agent',
                'title' => 'Espace agent',
                'description' => 'Traite les requetes assignees a votre service.',
                'features' => ['dashboard', 'suivi_requetes', 'process_etapes'],
                'quick_actions' => [
                    ['label' => 'Requetes a traiter', 'url' => '/agent/dashboard'],
                    ['label' => 'Historique', 'url' => '/agent/historique'],
                ],
            ],
        ];

        $profile = $profiles[$roleKey] ?? $profiles['autre'];

        return [
            'service_key' => $roleKey,
            'service_label' => $profile['label'],
            'title' => $profile['title'],
            'description' => $profile['description'],
            'features' => $profile['features'],
            'quick_actions' => $profile['quick_actions'],
        ];
    }

    public static function hasFeature(?Service $service, string $feature): bool
    {
        $profile = self::resolve($service);

        return in_array($feature, $profile['features'], true);
    }

    public static function resolveRoleKey(?Service $service): string
    {
        if (!$service) {
            return 'autre';
        }

        $normalizedType = self::normalize($service->type_service);
        $normalizedName = self::normalize($service->nom_service);

        if (in_array($normalizedType, ['courrier', 'servicecourrier'], true) || str_contains($normalizedName, 'courrier')) {
            return 'courrier';
        }
        if (in_array($normalizedType, ['scolarite'], true) || str_contains($normalizedName, 'scolarite')) {
            return 'scolarite';
        }
        if (in_array($normalizedType, ['conseilorientation'], true) || str_contains($normalizedName, 'orientation')) {
            return 'conseil_orientation';
        }
        if (in_array($normalizedType, ['da', 'directionadjointe'], true) || str_contains($normalizedName, 'adjointe')) {
            return 'da';
        }
        if (in_array($normalizedType, ['direction'], true) || str_contains($normalizedName, 'direction')) {
            return 'direction';
        }
        if (in_array($normalizedType, ['departement'], true) || str_contains($normalizedName, 'departement')) {
            return 'departement';
        }
        if (in_array($normalizedType, ['celluleinfo', 'celluleinformatique'], true) || str_contains($normalizedName, 'informatique')) {
            return 'cellule_info';
        }
        if (in_array($normalizedType, ['enseignant'], true) || str_contains($normalizedName, 'enseignant')) {
            return 'enseignant';
        }

        return 'autre';
    }

    private static function normalize(?string $value): string
    {
        $value = $value ?? '';
        $value = strtolower(trim($value));

        return preg_replace('/[^a-z0-9]/', '', $value) ?? '';
    }
}
