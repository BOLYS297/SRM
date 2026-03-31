#!/bin/bash
# Script de test complet de la navigation étudiant
# À exécuter: bash test_etudiant.sh

echo "========================================="
echo "🧪 Tests de Navigation Étudiant"
echo "========================================="
echo ""

# Base URL (adapter selon votre environnement)
BASE_URL="http://localhost:8000"

echo "📚 Étape 1: Authentification"
echo "────────────────────────────"

LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "jean.dupont@example.com",
    "password": "Password123!"
  }')

echo "Réponse: $LOGIN_RESPONSE"
echo ""

# Extraire le token (nécessite jq)
TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.token' 2>/dev/null)

if [ -z "$TOKEN" ] || [ "$TOKEN" = "null" ]; then
  echo "❌ Erreur: Impossible d'extraire le token"
  echo "   Assurez-vous que jq est installé: https://stedolan.github.io/jq/"
  echo ""
  echo "Alternative (sans jq):"
  echo "   Copiez le token de la réponse JSON ci-dessus et définissez:"
  echo "   TOKEN='votre_token_ici'"
  exit 1
fi

echo "✅ Token obtenu: ${TOKEN:0:20}..."
echo ""

# 2. Récupérer le profil
echo "📚 Étape 2: Profil Étudiant"
echo "────────────────────────────"

curl -s -X GET "$BASE_URL/api/etudiants/me" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq '.' 2>/dev/null || echo "Erreur lors de la récupération du profil"
echo ""

# 3. Dashboard
echo "📚 Étape 3: Dashboard Étudiant"
echo "────────────────────────────"

curl -s -X GET "$BASE_URL/api/dashboard/etudiant" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq '.' 2>/dev/null || echo "Erreur lors de la récupération du dashboard"
echo ""

# 4. Notifications
echo "📚 Étape 4: Notifications"
echo "────────────────────────────"

curl -s -X GET "$BASE_URL/api/notifications" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq '.' 2>/dev/null || echo "Erreur lors de la récupération des notifications"
echo ""

# 5. Requêtes
echo "📚 Étape 5: Requêtes"
echo "────────────────────────────"

curl -s -X GET "$BASE_URL/api/requetes" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq '.' 2>/dev/null || echo "Erreur lors de la récupération des requêtes"
echo ""

echo "========================================="
echo "✅ Tests complétés!"
echo "========================================="
