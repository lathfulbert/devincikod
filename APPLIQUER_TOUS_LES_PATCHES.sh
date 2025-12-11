#!/bin/bash
# Script d'application de tous les patches pour récupération des commits perdus
# Usage: bash APPLIQUER_TOUS_LES_PATCHES.sh

set -e  # Stop on error

echo "🚀 Début de l'application des patches..."
echo ""

# Couleurs pour le terminal
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Fonction pour afficher le statut
status() {
    echo -e "${GREEN}✓${NC} $1"
}

warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

error() {
    echo -e "${RED}✗${NC} $1"
}

# Vérifier qu'on est dans le bon dossier
if [ ! -f "composer.json" ]; then
    error "Erreur: Exécutez ce script depuis la racine du projet"
    exit 1
fi

echo "📋 Liste des modifications à appliquer:"
echo "  1. Menu Wallet (déjà fait)"
echo "  2. FileImportService.php (déjà fait)"
echo "  3. DashboardController statistics()"
echo "  4. Chart.js v3.9.1"
echo "  5. master.php startTime fix"
echo "  6. SmsController coûts"
echo "  7. SmsQueueService coûts"
echo "  8. SmsOtpProvider coûts"
echo "  9. SmsCoreModule route"
echo "  10. SmsController parseFile()"
echo ""

read -p "Continuer? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    exit 1
fi

# ============================================================================
# 1. Menu Wallet
# ============================================================================
status "1. Menu Wallet - Déjà appliqué ✓"

# ============================================================================
# 2. FileImportService
# ============================================================================
status "2. FileImportService - Déjà appliqué ✓"

# ============================================================================
# 3. Télécharger Chart.js v3.9.1
# ============================================================================
echo ""
echo "📦 Téléchargement de Chart.js v3.9.1..."

if [ ! -d "public/assets/js/chart/chartjs" ]; then
    mkdir -p public/assets/js/chart/chartjs
fi

curl -s -o public/assets/js/chart/chartjs/chart-v3.min.js \
    https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js

if [ -f "public/assets/js/chart/chartjs/chart-v3.min.js" ]; then
    status "Chart.js v3.9.1 téléchargé"
else
    error "Échec du téléchargement de Chart.js"
    exit 1
fi

# ============================================================================
# 4. Fix master.php startTime
# ============================================================================
echo ""
echo "🔧 Fix master.php startTime..."

MASTER_FILE="resources/views/backend/layouts/master.php"

if [ -f "$MASTER_FILE" ]; then
    # Backup
    cp "$MASTER_FILE" "$MASTER_FILE.backup_$(date +%Y%m%d_%H%M%S)"

    # Replace
    sed -i 's/<body onload="startTime()">/<body onload="if(typeof startTime === '\''function'\'') startTime()">/' "$MASTER_FILE"

    status "master.php modifié"
else
    warning "master.php non trouvé - À modifier manuellement"
fi

# ============================================================================
# Message final
# ============================================================================
echo ""
echo "============================================================================"
echo "✅ Patches automatiques appliqués!"
echo ""
echo "⚠️  MODIFICATIONS MANUELLES REQUISES:"
echo ""
echo "1️⃣  DashboardController.php - statistics():"
echo "   → Remplacer la méthode statistics() avec le contenu de:"
echo "   → PATCH_FILES/DashboardController_statistics.php"
echo ""
echo "2️⃣  SmsController.php - Coûts SMS:"
echo "   → Voir PATCH_FILES/SmsController_cost_recording.php"
echo "   → 3 modifications à faire dans ce fichier"
echo ""
echo "3️⃣  SmsQueueService.php - sms_messages:"
echo "   → Voir PATCH_FILES/SmsQueueService_cost_recording.php"
echo ""
echo "4️⃣  SmsOtpProvider.php - Coûts OTP:"
echo "   → Voir PATCH_FILES/SmsOtpProvider_cost_recording.php"
echo ""
echo "5️⃣  SmsCoreModule.php - Route parse-file:"
echo "   → Ajouter: ['POST', '/admin/sms/parse-file', ...]"
echo ""
echo "6️⃣  SmsController.php - Méthode parseFile():"
echo "   → Nouvelle méthode complète à ajouter"
echo ""
echo "📖 Consultez GUIDE_RECUPERATION_COMPLETE.md pour les détails"
echo "============================================================================"
echo ""

# Vérification syntaxe PHP
echo "🔍 Vérification syntaxe PHP..."
php -l Modules/SmsCore/Services/FileImportService.php
php -l Modules/Wallet/WalletModule.php

echo ""
status "Script terminé! Appliquez les modifications manuelles restantes."
