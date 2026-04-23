<?php
/**
 * Script de génération des icônes PWA
 * Crée des icônes en utilisant GD
 */

if (!extension_loaded('gd')) {
    die("GD extension is required.\n");
}

$publicPath = __DIR__ . '/public/images/icons';
if (!is_dir($publicPath)) {
    mkdir($publicPath, 0755, true);
}

function generateIcon($size) {
    $image = imagecreatetruecolor($size, $size);
    imagealphablending($image, false);
    imagesavealpha($image, true);

    $white = imagecolorallocate($image, 255, 255, 255);
    $teal = imagecolorallocate($image, 31, 122, 108);
    $accent = imagecolorallocate($image, 239, 107, 58);

    // Fond blanc
    imagefilledrectangle($image, 0, 0, $size, $size, $white);

    // Carré de couleur teal avec coins arrondis
    $margin = intval($size * 0.1);
    imagefilledrectangle($image, $margin, $margin, $size - $margin, $size - $margin, $teal);

    // Accent diagonal
    $points = [
        intval($size * 0.3), intval($size * 0.2),
        intval($size * 0.7), intval($size * 0.2),
        intval($size * 0.7), intval($size * 0.8),
        intval($size * 0.3), intval($size * 0.8),
    ];
    imagefilledpolygon($image, $points, $accent);

    return $image;
}

// Générer icônes
$sizes = [96, 192, 512];
foreach ($sizes as $size) {
    $img = generateIcon($size);
    imagepng($img, "$publicPath/icon-{$size}x{$size}.png");
    imagedestroy($img);
    echo "✓ icon-{$size}x{$size}.png\n";
}

// Icône maskable
$img = generateIcon(192);
imagepng($img, "$publicPath/icon-192x192-maskable.png");
imagedestroy($img);
echo "✓ icon-192x192-maskable.png\n";

// Apple touch icon
$img = generateIcon(180);
imagepng($img, "$publicPath/apple-touch-icon.png");
imagedestroy($img);
echo "✓ apple-touch-icon.png\n";

// Screenshots
$img = generateIcon(540);
imagepng($img, "$publicPath/screenshot-540x720.png");
imagedestroy($img);
echo "✓ screenshot-540x720.png\n";

$img = generateIcon(1280);
imagepng($img, "$publicPath/screenshot-1280x720.png");
imagedestroy($img);
echo "✓ screenshot-1280x720.png\n";

echo "\n✅ PWA icons generated!\n";
