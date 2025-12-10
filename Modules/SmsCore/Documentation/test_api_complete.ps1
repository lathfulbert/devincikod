# =============================================================================
# Script de Test Complet - API SMS (PowerShell)
# =============================================================================
# Ce script teste tous les endpoints de l'API SMS avec une clé API valide
#
# Prérequis:
# - Remplacer YOUR_API_KEY par votre vraie clé API
# - Le serveur doit être accessible sur localhost:81
#
# Usage: .\test_api_complete.ps1
# =============================================================================

# Configuration
$API_KEY = "sk_ff13d4639f6491120e3c94dd584a6e282c9fa82994d4424edd7971ff3b97377c"
$BASE_URL = "http://localhost:81/sunuframework2"

Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host "           TEST COMPLET - API SMS (Laravel Sanctum Style)" -ForegroundColor Cyan
Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host ""

# =============================================================================
# Test 1: GET /api/v1/sms/balance
# =============================================================================
Write-Host "[1/4] Test: GET /api/v1/sms/balance" -ForegroundColor Blue
Write-Host "----------------------------------------"

try {
    $headers = @{
        "Authorization" = "Bearer $API_KEY"
        "Content-Type" = "application/json"
    }

    $response = Invoke-RestMethod -Uri "$BASE_URL/api/v1/sms/balance" -Method Get -Headers $headers
    $response | ConvertTo-Json -Depth 10

    if ($response.success) {
        Write-Host "✓ Test réussi" -ForegroundColor Green
    } else {
        Write-Host "✗ Test échoué" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Erreur: $_" -ForegroundColor Red
}
Write-Host ""

# =============================================================================
# Test 2: GET /api/v1/sms/history
# =============================================================================
Write-Host "[2/4] Test: GET /api/v1/sms/history" -ForegroundColor Blue
Write-Host "----------------------------------------"

try {
    $response = Invoke-RestMethod -Uri "$BASE_URL/api/v1/sms/history?limit=3" -Method Get -Headers $headers
    $response | ConvertTo-Json -Depth 10

    if ($response.success) {
        Write-Host "✓ Test réussi" -ForegroundColor Green
    } else {
        Write-Host "✗ Test échoué" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Erreur: $_" -ForegroundColor Red
}
Write-Host ""

# =============================================================================
# Test 3: GET /api/v1/sms/sender-names
# =============================================================================
Write-Host "[3/4] Test: GET /api/v1/sms/sender-names" -ForegroundColor Blue
Write-Host "----------------------------------------"

try {
    $response = Invoke-RestMethod -Uri "$BASE_URL/api/v1/sms/sender-names" -Method Get -Headers $headers
    $response | ConvertTo-Json -Depth 10

    if ($response.success) {
        Write-Host "✓ Test réussi" -ForegroundColor Green
    } else {
        Write-Host "✗ Test échoué" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Erreur: $_" -ForegroundColor Red
}
Write-Host ""

# =============================================================================
# Test 4: POST /api/v1/sms/send
# =============================================================================
Write-Host "[4/4] Test: POST /api/v1/sms/send" -ForegroundColor Blue
Write-Host "----------------------------------------"

try {
    $body = @{
        to = "+2250709876543"
        message = "Test automatisé depuis PowerShell"
        sender_id = "AKADI"
    } | ConvertTo-Json

    $response = Invoke-RestMethod -Uri "$BASE_URL/api/v1/sms/send" -Method Post -Headers $headers -Body $body
    $response | ConvertTo-Json -Depth 10

    if ($response.success) {
        Write-Host "✓ Test réussi" -ForegroundColor Green
    } else {
        Write-Host "✗ Test échoué" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Erreur: $_" -ForegroundColor Red
}
Write-Host ""

# =============================================================================
# Test 5: Authentification par Query Parameter
# =============================================================================
Write-Host "[Bonus] Test: Authentification par Query Parameter" -ForegroundColor Blue
Write-Host "----------------------------------------"

try {
    $response = Invoke-RestMethod -Uri "$BASE_URL/api/v1/sms/balance?api_key=$API_KEY" -Method Get
    $response | ConvertTo-Json -Depth 10

    if ($response.success) {
        Write-Host "✓ Query parameter fonctionne" -ForegroundColor Green
    } else {
        Write-Host "✗ Query parameter échoué" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Erreur: $_" -ForegroundColor Red
}
Write-Host ""

# =============================================================================
# Résumé
# =============================================================================
Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host "                         FIN DES TESTS" -ForegroundColor Cyan
Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Tous les endpoints ont été testés!"
Write-Host ""
Write-Host "Endpoints disponibles:" -ForegroundColor Yellow
Write-Host "  • GET  /api/v1/sms/balance        - Consulter le solde"
Write-Host "  • GET  /api/v1/sms/history        - Historique des SMS"
Write-Host "  • GET  /api/v1/sms/sender-names   - Liste des sender names"
Write-Host "  • POST /api/v1/sms/send           - Envoyer un SMS"
Write-Host ""
Write-Host "Méthodes d'authentification supportées:" -ForegroundColor Yellow
Write-Host "  • Authorization: Bearer {api_key}"
Write-Host "  • Query parameter: ?api_key={api_key}"
Write-Host "  • POST data: api_key={api_key}"
Write-Host "  • JSON body: {`"api_key`": `"{api_key}`"}"
Write-Host ""
Write-Host "Documentation complète: /admin/sms/api/docs"
Write-Host "======================================================================="
