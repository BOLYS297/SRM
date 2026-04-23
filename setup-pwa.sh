#!/bin/bash
# Générer les icônes PWA
php generate-pwa-icons.php

echo ""
echo "========================================"
echo "✅ PWA Configuration Complete!"
echo "========================================"
echo ""
echo "Étapes pour builder et servir:"
echo "1. npm install           # Installer les dépendances"
echo "2. npm run build         # Builder avec Vite"
echo "3. php artisan serve     # Démarrer le serveur"
echo ""
echo "La PWA sera accessible sur http://localhost:8000"
echo ""
echo "Pour tester sur mobile:"
echo "  - Android: Chrome > Menu > Installer l'app"
echo "  - iOS: Safari > Partage > Sur l'écran d'accueil"
echo ""
