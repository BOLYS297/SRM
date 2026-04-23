@echo off
REM Script Windows pour configurer la PWA
cls
echo.
echo ======================================
echo Generateur d'icones PWA
echo ======================================
echo.

REM Générer les icônes
php generate-pwa-icons.php

echo.
echo ========================================
echo Configuration PWA Completee!
echo ========================================
echo.
echo Commandes pour demarrer:
echo   1. npm install
echo   2. npm run build
echo   3. php artisan serve
echo.
echo La PWA sera accessible sur:
echo   http://localhost:8000
echo.
echo Pour tester sur mobile:
echo   - Android: Chrome ^> Menu ^> Installer l'app
echo   - iOS: Safari ^> Partage ^> Sur l'ecran d'accueil
echo.
pause
