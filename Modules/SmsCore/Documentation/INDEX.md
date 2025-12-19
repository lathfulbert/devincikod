# Documentation Module SMS - Index

Bienvenue dans la documentation complète du module SMS.

## 📚 Documentation disponible

### Pour les utilisateurs

1. **[README_API.md](README_API.md)** - Guide de démarrage rapide API
   - Comment générer une clé API
   - Exemples d'utilisation rapides
   - Premiers pas avec l'API

2. **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - Documentation API complète
   - Tous les endpoints détaillés
   - Exemples de code dans plusieurs langages
   - Codes d'erreur et réponses
   - Limites et quotas
   - Guide de tarification

### Pour les administrateurs

3. **[ADMIN_GUIDE.md](ADMIN_GUIDE.md)** - Guide administrateur complet
   - Configuration initiale du module
   - Gestion des utilisateurs et permissions
   - Configuration des tarifs
   - Gestion des Sender Names
   - Monitoring et statistiques
   - Dépannage

### Documentation technique

4. **[ENDPOINTS_SUMMARY.md](ENDPOINTS_SUMMARY.md)** - Récapitulatif technique
   - Liste complète de toutes les routes
   - Routes API
   - Routes Web
   - Middlewares et permissions
   - Services disponibles
   - Modèles de données

5. **[README_SENDER_NAMES.md](README_SENDER_NAMES.md)** - Système Sender Names
   - Architecture du système
   - Base de données
   - Utilisation des modèles
   - Routes disponibles

6. **[IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)** - Résumé d'implémentation
   - Structure des tables
   - Controllers créés
   - Routes définies
   - État des vues

### Outils et tests

7. **[test_api.php](test_api.php)** - Script de test API
   - Script PHP pour tester les endpoints
   - Tests automatisés
   - Validation de l'authentification

8. **[QUICK_COMMANDS.md](QUICK_COMMANDS.md)** - Commandes SQL rapides
   - Requêtes utiles
   - Opérations CRUD
   - Scripts de maintenance

---

## 🚀 Par où commencer ?

### Je suis un utilisateur qui veut utiliser l'API

1. Lisez le [README_API.md](README_API.md) pour commencer
2. Consultez l'[API_DOCUMENTATION.md](API_DOCUMENTATION.md) pour les détails
3. Testez avec [test_api.php](test_api.php)

### Je suis administrateur

1. Suivez le [ADMIN_GUIDE.md](ADMIN_GUIDE.md) pour la configuration
2. Consultez [ENDPOINTS_SUMMARY.md](ENDPOINTS_SUMMARY.md) pour comprendre l'architecture
3. Utilisez [QUICK_COMMANDS.md](QUICK_COMMANDS.md) pour la maintenance

### Je suis développeur

1. Lisez [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) pour l'architecture
2. Consultez [ENDPOINTS_SUMMARY.md](ENDPOINTS_SUMMARY.md) pour les routes
3. Étudiez [README_SENDER_NAMES.md](README_SENDER_NAMES.md) pour le système de sender names

---

## 📍 Accès rapide (Interface Web)

| Page | URL | Description |
|------|-----|-------------|
| Dashboard SMS | `/admin/sms` | Vue d'ensemble |
| Envoyer SMS | `/admin/sms/send` | Envoi simple |
| Envoi groupé | `/admin/sms/bulk` | Envoi en masse |
| Historique | `/admin/sms/history` | Tous les SMS |
| Statistiques | `/admin/sms/statistics` | Graphiques et métriques |
| Campagnes | `/admin/sms/campaigns` | Gestion campagnes |
| Sender Names | `/sms/sender-names` | Noms d'expéditeur |
| Tarification | `/admin/sms/pricing` | Grille tarifaire |
| Facturation | `/admin/sms/billing` | Logs facturation |
| **Documentation API** | **`/admin/sms/api/docs`** | **Docs interactive** |
| **Mes clés API** | **`/admin/apikeys`** | **Gestion clés** |

---

## 🔥 Endpoints API principaux

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/v1/sms/send` | Envoyer un SMS |
| GET | `/api/v1/sms/history` | Consulter l'historique |
| GET | `/api/v1/sms/balance` | Consulter le solde |

**Authentification :** Header `Authorization: Bearer VOTRE_CLE_API`

---

## 📊 Architecture du module

```
SmsCore/
├── Controllers/          # Contrôleurs (API, Web, Dashboard)
├── Models/              # Modèles de données
├── Services/            # Logique métier
├── Gateways/            # Intégrations providers SMS
├── Views/               # Templates frontend
├── Routes/              # Définition des routes
├── Database/            # Migrations
├── Documentation/       # 📁 VOUS ÊTES ICI
├── Cron/               # Tâches planifiées
└── SmsCoreModule.php   # Point d'entrée module
```

---

## 🔐 Permissions RBAC

Permissions principales à configurer :
- `sms.send` - Envoyer des SMS
- `sms.history.view` - Voir l'historique
- `sms.stats.view` - Voir les statistiques
- `sms.campaigns.create` - Créer des campagnes
- `sms.sender_names.manage` - Gérer les sender names

---

## 🛠️ Technologies utilisées

- **Backend :** PHP 8.0+
- **Base de données :** MySQL/MariaDB
- **Gateways :** Orange CI, Infobip
- **Authentication :** API Key (Bearer token)
- **Authorization :** RBAC (Role-Based Access Control)

---

## 📞 Support

- **Interface de documentation :** `/admin/sms/api/docs`
- **Gestion des clés :** `/admin/apikeys`
- **Email :** support@votre-domaine.com

---

## 📝 Changelog

### Version 2.0 (Décembre 2025)
- ✅ Documentation API complète
- ✅ Interface web pour gérer les clés API
- ✅ Système de Sender Names avec RBAC
- ✅ Dashboard et statistiques avancées
- ✅ Support multi-gateway
- ✅ Facturation automatique
- ✅ Campagnes SMS avec personnalisation

### Version 1.0 (Novembre 2025)
- Envoi SMS basique
- Historique
- Gateway Orange CI

---

**Dernière mise à jour :** 10 Décembre 2025
