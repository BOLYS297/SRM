<?php
/**
 * Script pour remplacer toutes les icônes PWA par des versions du logo
 */

if (!extension_loaded('gd')) {
    die("GD extension is required.\n");
}

$iconsDir = __DIR__ . '/public/images/icons';
$logoPath = $iconsDir . '/icon-512x512-maskable.png';

if (!file_exists($logoPath)) {
    die("Logo source not found: $logoPath\n");
}

// Charger le logo source
$logo = imagecreatefrompng($logoPath);
if (!$logo) {
    die("Failed to load logo image.\n");
}

$logoWidth = imagesx($logo);
$logoHeight = imagesy($logo);

// Tailles d'icônes à générer
$sizes = [
    'icon-96x96.png' => 96,
    'icon-192x192.png' => 192,
    'icon-192x192-maskable.png' => 192,
    'icon-512x512.png' => 512,
    'icon-512x512-maskable.png' => 512,
    'apple-touch-icon.png' => 180,
    'screenshot-540x720.png' => 540,
    'screenshot-1280x720.png' => 1280,
];

foreach ($sizes as $filename => $size) {
    $filepath = $iconsDir . '/' . $filename;

    // Créer une nouvelle image carrée
    $newImage = imagecreatetruecolor($size, $size);
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);

    // Fond transparent
    $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
    imagefill($newImage, 0, 0, $transparent);

    // Redimensionner le logo
    imagecopyresampled($newImage, $logo, 0, 0, 0, 0, $size, $size, $logoWidth, $logoHeight);

    // Sauvegarder
    if (imagepng($newImage, $filepath)) {
        echo "✓ Updated: $filename ({$size}x{$size})\n";
    } else {
        echo "✗ Failed: $filename\n";
    }

    imagedestroy($newImage);
}

imagedestroy($logo);

echo "\n✅ All PWA icons updated with logo!\n";
?>
