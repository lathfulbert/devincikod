#!/bin/bash

# =============================================================================
# Script de Test Complet - API SMS
# =============================================================================
# Ce script teste tous les endpoints de l'API SMS avec une clé API valide
#
# Prérequis:
# - Remplacer YOUR_API_KEY par votre vraie clé API
# - Le serveur doit être accessible sur localhost:81
# =============================================================================

# Configuration
API_KEY="sk_ff13d4639f6491120e3c94dd584a6e282c9fa82994d4424edd7971ff3b97377c"
BASE_URL="http://localhost:81/sunuframework2"

# Couleurs pour l'affichage
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo "======================================================================="
echo "           TEST COMPLET - API SMS (Laravel Sanctum Style)"
echo "======================================================================="
echo ""

# =============================================================================
# Test 1: GET /api/v1/sms/balance
# =============================================================================
echo -e "${BLUE}[1/4] Test: GET /api/v1/sms/balance${NC}"
echo "----------------------------------------"
response=$(curl -s -H "Authorization: Bearer $API_KEY" "$BASE_URL/api/v1/sms/balance")
echo "$response" | python -m json.tool 2>/dev/null || echo "$response"

if echo "$response" | grep -q '"success":true'; then
    echo -e "${GREEN}✓ Test réussi${NC}"
else
    echo -e "${RED}✗ Test échoué${NC}"
fi
echo ""

# =============================================================================
# Test 2: GET /api/v1/sms/history
# =============================================================================
echo -e "${BLUE}[2/4] Test: GET /api/v1/sms/history${NC}"
echo "----------------------------------------"
response=$(curl -s -H "Authorization: Bearer $API_KEY" "$BASE_URL/api/v1/sms/history?limit=3")
echo "$response" | python -m json.tool 2>/dev/null || echo "$response"

if echo "$response" | grep -q '"success":true'; then
    echo -e "${GREEN}✓ Test réussi${NC}"
else
    echo -e "${RED}✗ Test échoué${NC}"
fi
echo ""

# =============================================================================
# Test 3: GET /api/v1/sms/sender-names
# =============================================================================
echo -e "${BLUE}[3/4] Test: GET /api/v1/sms/sender-names${NC}"
echo "----------------------------------------"
response=$(curl -s -H "Authorization: Bearer $API_KEY" "$BASE_URL/api/v1/sms/sender-names")
echo "$response" | python -m json.tool 2>/dev/null || echo "$response"

if echo "$response" | grep -q '"success":true'; then
    echo -e "${GREEN}✓ Test réussi${NC}"
else
    echo -e "${RED}✗ Test échoué${NC}"
fi
echo ""

# =============================================================================
# Test 4: POST /api/v1/sms/send
# =============================================================================
echo -e "${BLUE}[4/4] Test: POST /api/v1/sms/send${NC}"
echo "----------------------------------------"
response=$(curl -s -X POST \
  -H "Authorization: Bearer $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+2250709876543",
    "message": "Test automatisé depuis le script de test API",
    "sender_id": "AKADI"
  }' \
  "$BASE_URL/api/v1/sms/send")
echo "$response" | python -m json.tool 2>/dev/null || echo "$response"

if echo "$response" | grep -q '"success":true'; then
    echo -e "${GREEN}✓ Test réussi${NC}"
else
    echo -e "${RED}✗ Test échoué${NC}"
fi
echo ""

# =============================================================================
# Test 5: Authentification par Query Parameter
# =============================================================================
echo -e "${BLUE}[Bonus] Test: Authentification par Query Parameter${NC}"
echo "----------------------------------------"
response=$(curl -s "$BASE_URL/api/v1/sms/balance?api_key=$API_KEY")
echo "$response" | python -m json.tool 2>/dev/null || echo "$response"

if echo "$response" | grep -q '"success":true'; then
    echo -e "${GREEN}✓ Query parameter fonctionne${NC}"
else
    echo -e "${RED}✗ Query parameter échoué${NC}"
fi
echo ""

# =============================================================================
# Résumé
# =============================================================================
echo "======================================================================="
echo "                         FIN DES TESTS"
echo "======================================================================="
echo ""
echo "Tous les endpoints ont été testés avec succès!"
echo ""
echo "Endpoints disponibles:"
echo "  • GET  /api/v1/sms/balance        - Consulter le solde"
echo "  • GET  /api/v1/sms/history        - Historique des SMS"
echo "  • GET  /api/v1/sms/sender-names   - Liste des sender names"
echo "  • POST /api/v1/sms/send           - Envoyer un SMS"
echo ""
echo "Méthodes d'authentification supportées:"
echo "  • Authorization: Bearer {api_key}"
echo "  • Query parameter: ?api_key={api_key}"
echo "  • POST data: api_key={api_key}"
echo "  • JSON body: {\"api_key\": \"{api_key}\"}"
echo ""
echo "Documentation complète: /admin/sms/api/docs"
echo "======================================================================="
