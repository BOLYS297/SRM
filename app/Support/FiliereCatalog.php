<?php

namespace App\Support;

class FiliereCatalog
{
    private const FILIERES = [
        ['code' => 'GC', 'libelle' => 'Genie Civil'],
        ['code' => 'GE', 'libelle' => 'Genie Electrique'],
        ['code' => 'GI', 'libelle' => 'Genie Informatique'],
        ['code' => 'GRT', 'libelle' => 'Genie Reseau et Telecommunications'],
        ['code' => 'GEII', 'libelle' => 'Genie Electrique et Informatique Industrielle'],
        ['code' => 'GIM', 'libelle' => 'Genie Industriel et Maintenance'],
        ['code' => 'GMP', 'libelle' => 'Genie Mecanique et Productique'],
        ['code' => 'GTE', 'libelle' => 'Genie Thermique et Energie'],
        ['code' => 'TC', 'libelle' => 'Techniques de Commercialisation'],
        ['code' => 'GLT', 'libelle' => 'Genie Logistique et Transport'],
        ['code' => 'GEA', 'libelle' => 'Gestion des Entreprises et Administrations'],
        ['code' => 'OGA', 'libelle' => 'Organisation et Gestion Administrative'],
        ['code' => 'GAPMO', 'libelle' => 'Gestion Appliquee aux Petites et Moyennes Organisations'],
        ['code' => 'GL', 'libelle' => 'Genie Logiciel'],
        ['code' => 'ASR', 'libelle' => 'Administration Securite Reseaux'],
        ['code' => 'GCF', 'libelle' => 'Gestion Comptable et Financiere'],
        ['code' => 'GRH', 'libelle' => 'Gestion des Ressources Humaines'],
        ['code' => 'LI', 'libelle' => 'Logistique Industrielle'],
        ['code' => 'PG', 'libelle' => 'Petrole et Gaz'],
        ['code' => 'GM', 'libelle' => 'Genie Mecanique'],
        ['code' => 'GMINES', 'libelle' => 'Genie des Mines'],
        ['code' => 'GB', 'libelle' => 'Genie Biomedical'],
    ];

    public static function all(): array
    {
        return self::FILIERES;
    }

    public static function codes(): array
    {
        return array_column(self::FILIERES, 'code');
    }

    public static function labelsByCode(): array
    {
        $labels = [];
        foreach (self::FILIERES as $filiere) {
            $labels[$filiere['code']] = $filiere['libelle'];
        }

        return $labels;
    }

    public static function labelForCode(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        $labels = self::labelsByCode();
        $normalized = self::normalizeCode($code);

        return $normalized ? ($labels[$normalized] ?? null) : null;
    }

    public static function normalizeCode(?string $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $codes = self::codesSortedByLengthDesc();
        foreach ($codes as $code) {
            if (preg_match('/\\b' . preg_quote($code, '/') . '\\b/i', $raw) === 1) {
                return $code;
            }
        }

        $prefix = preg_split('/[\\s\\-:\\/]/', strtoupper($raw))[0] ?? '';
        $prefix = preg_replace('/[^A-Z0-9]/', '', $prefix) ?? '';

        if ($prefix !== '' && in_array($prefix, self::codes(), true)) {
            return $prefix;
        }

        $allNormalized = preg_replace('/[^A-Z0-9]/', '', strtoupper($raw)) ?? '';
        if ($allNormalized !== '' && in_array($allNormalized, self::codes(), true)) {
            return $allNormalized;
        }

        if ($allNormalized === 'GMINES') {
            return 'GMINES';
        }

        return null;
    }

    private static function codesSortedByLengthDesc(): array
    {
        $codes = self::codes();
        usort($codes, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        return $codes;
    }
}
