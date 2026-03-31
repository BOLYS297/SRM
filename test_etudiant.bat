@REM Tests de Navigation Étudiant pour Windows
@REM À exécuter: test_etudiant.bat

@echo off
cls
echo =========================================
echo ^🧪 Tests de Navigation Etudiant
echo =========================================
echo.

setlocal enabledelayedexpansion

REM Configuration
set BASE_URL=http://localhost:8000
set EMAIL=jean.dupont@example.com
set PASSWORD=Password123!

echo 📚 Preconditions:
echo ────────────────────────────
echo - URL: %BASE_URL%
echo - Email: %EMAIL%
echo - Mot de passe: %PASSWORD%
echo.
echo Note: Installez 'curl' et un parser JSON pour tirer parti complet de ce script.
echo       Sur Windows 11+, curl est inclus. Vous pouvez installer jq via chocolatey.
echo.

echo 📚 Etape 1: Authentification
echo ────────────────────────────

curl -s -X POST "%BASE_URL%/api/login" ^
  -H "Content-Type: application/json" ^
  -d "{\"email\": \"%EMAIL%\", \"password\": \"%PASSWORD%\"}"

echo.
echo.

echo 📚 Les tests API suivants nécessitent le token d'authentification.
echo Veuillez:
echo 1. Copier le 'token' de la réponse ci-dessus
echo 2. Définir: set TOKEN=votre_token_ici
echo 3. Exécuter les commandes curl suivantes:
echo.
echo Exemple pour le dashboard:
echo ────────────────────────────
echo curl -s -X GET "%BASE_URL%/api/dashboard/etudiant" ^
echo   -H "Authorization: Bearer [TOKEN]" ^
echo   -H "Content-Type: application/json"
echo.
echo.

echo 📚 Autres endpoints etudiant disponibles:
echo ────────────────────────────
echo [GET]  %BASE_URL%/api/etudiants/me              - Profil
echo [GET]  %BASE_URL%/api/dashboard/etudiant       - Dashboard avec stats
echo [GET]  %BASE_URL%/api/notifications             - Notifications
echo [GET]  %BASE_URL%/api/services                  - Services disponibles
echo [GET]  %BASE_URL%/api/types-requetes           - Types de requetes
echo [GET]  %BASE_URL%/api/requetes                 - Toutes les requetes
echo [GET]  %BASE_URL%/api/requetes/{id}            - Details d'une requete
echo.

echo =========================================
echo ✅ Identifiants de connexion:
echo =========================================
echo Email:    %EMAIL%
echo Password: %PASSWORD%
echo =========================================

pause
