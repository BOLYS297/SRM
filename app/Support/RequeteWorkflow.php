<?php

namespace App\Support;

use App\Models\Requete;
use App\Models\Service;
use App\Models\TypeRequete;

class RequeteWorkflow
{
    private const STANDARD_PATH = ['courrier', 'direction', 'da', 'departement', 'cellule_info', 'scolarite'];

    private const SPECIAL_PATHS = [
        'demandedecorrectionnomprenomdatedenaissanceetc' => ['courrier', 'scolarite', 'cellule_info'],
        'absencedenotedecc' => ['courrier', 'scolarite', 'departement', 'enseignant'],
        'absencedenotesurlespv' => ['courrier', 'scolarite', 'departement'],
        'absencedenomsurlespv' => ['courrier', 'scolarite', 'departement'],
    ];

    public static function entryService(?TypeRequete $typeRequete): ?Service
    {
        $path = self::pathForType($typeRequete);
        if (empty($path)) {
            return null;
        }

        return self::serviceForKey($path[0]);
    }

    public static function nextService(Requete $requete, Service $currentService): ?Service
    {
        $path = self::pathForType($requete->typeRequete);
        if (empty($path)) {
            return null;
        }

        $currentKey = self::serviceKey($currentService);
        if (!$currentKey) {
            return null;
        }

        $index = array_search($currentKey, $path, true);
        if ($index === false) {
            return null;
        }

        $nextKey = $path[$index + 1] ?? null;
        if (!$nextKey) {
            return null;
        }

        if ($nextKey === 'departement') {
            return self::serviceForDepartementFiliere($requete);
        }

        return self::serviceForKey($nextKey);
    }

    public static function isAllowedNextService(Requete $requete, Service $currentService, Service $candidate): bool
    {
        $expected = self::nextService($requete, $currentService);
        if (!$expected) {
            return false;
        }

        return (int) $expected->id === (int) $candidate->id;
    }

    public static function serviceKey(Service $service): ?string
    {
        $normalizedType = self::normalize($service->type_service);
        $normalizedName = self::normalize($service->nom_service);

        $map = self::serviceMap();

        foreach ($map as $key => $candidates) {
            foreach ($candidates['types'] as $type) {
                if ($normalizedType !== '' && $normalizedType === self::normalize($type)) {
                    return $key;
                }
            }
        }

        foreach ($map as $key => $candidates) {
            foreach ($candidates['names'] as $name) {
                if (str_contains($normalizedName, self::normalize($name))) {
                    return $key;
                }
            }
        }

        return null;
    }

    private static function pathForType(?TypeRequete $typeRequete): array
    {
        $normalizedLabel = self::normalize($typeRequete?->libelle);
        if (isset(self::SPECIAL_PATHS[$normalizedLabel])) {
            return self::SPECIAL_PATHS[$normalizedLabel];
        }

        return self::STANDARD_PATH;
    }

    private static function serviceForKey(string $key): ?Service
    {
        $map = self::serviceMap();
        $candidates = $map[$key] ?? null;
        if (!$candidates) {
            return null;
        }

        $query = Service::query();
        $query->where(function ($subQuery) use ($candidates) {
            foreach ($candidates['types'] as $type) {
                $subQuery->orWhereRaw(
                    'LOWER(REPLACE(REPLACE(COALESCE(type_service, ?), ?, ?), ?, ?)) = ?',
                    ['', ' ', '', '_', '', self::normalize($type)]
                );
            }
            foreach ($candidates['names'] as $name) {
                $subQuery->orWhereRaw(
                    'LOWER(REPLACE(REPLACE(COALESCE(nom_service, ?), ?, ?), ?, ?)) LIKE ?',
                    ['', ' ', '', '_', '', '%' . self::normalize($name) . '%']
                );
            }
        });

        return $query->orderBy('id')->first();
    }

    private static function serviceForDepartementFiliere(Requete $requete): ?Service
    {
        $code = FiliereCatalog::normalizeCode($requete->filiere_depot);
        if ($code) {
            $byCode = Service::query()
                ->whereRaw(
                    'LOWER(REPLACE(REPLACE(COALESCE(type_service, ?), ?, ?), ?, ?)) = ?',
                    ['', ' ', '', '_', '', self::normalize('Departement')]
                )
                ->whereRaw('UPPER(COALESCE(code_departement, ?)) = ?', ['', $code])
                ->orderBy('id')
                ->first();
            if ($byCode) {
                return $byCode;
            }

            $byName = Service::query()
                ->whereRaw(
                    'LOWER(REPLACE(REPLACE(COALESCE(type_service, ?), ?, ?), ?, ?)) = ?',
                    ['', ' ', '', '_', '', self::normalize('Departement')]
                )
                ->whereRaw('UPPER(COALESCE(nom_service, ?)) LIKE ?', ['', '%' . $code . '%'])
                ->orderBy('id')
                ->first();
            if ($byName) {
                return $byName;
            }
        }

        return self::serviceForKey('departement');
    }

    private static function serviceMap(): array
    {
        return [
            'courrier' => [
                'types' => ['Courrier', 'ServiceCourrier'],
                'names' => ['courrier'],
            ],
            'scolarite' => [
                'types' => ['Scolarite'],
                'names' => ['scolarite'],
            ],
            'cellule_info' => [
                'types' => ['CelluleInfo', 'CelluleInformatique'],
                'names' => ['informatique', 'celluleinfo'],
            ],
            'departement' => [
                'types' => ['Departement'],
                'names' => ['departement'],
            ],
            'enseignant' => [
                'types' => ['Enseignant'],
                'names' => ['enseignant'],
            ],
            'direction' => [
                'types' => ['Direction'],
                'names' => ['direction'],
            ],
            'da' => [
                'types' => ['DA', 'DirectionAdjointe'],
                'names' => ['adjointe', 'da'],
            ],
        ];
    }

    private static function normalize(?string $value): string
    {
        $value = $value ?? '';
        $value = strtolower(trim($value));

        return preg_replace('/[^a-z0-9]/', '', $value) ?? '';
    }
}
