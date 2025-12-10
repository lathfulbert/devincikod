# Guide de démarrage rapide - API SMS

## 🚀 Comment commencer

### 1. Générer votre clé API

1. Connectez-vous à votre compte
2. Accédez à **Mes clés API** (`/admin/api-keys`)
3. Cliquez sur "Générer une clé API"
4. Copiez votre clé et conservez-la en sécurité

### 2. Tester l'API

**Envoi d'un SMS simple :**

```bash
curl -X POST https://votre-domaine.com/api/v1/sms/send \
  -H "Authorization: Bearer VOTRE_CLE_API" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+225XXXXXXXX",
    "message": "Test SMS"
  }'
```

**Consulter l'historique :**

```bash
curl -X GET "https://votre-domaine.com/api/v1/sms/history?limit=10" \
  -H "Authorization: Bearer VOTRE_CLE_API"
```

**Vérifier le solde :**

```bash
curl -X GET https://votre-domaine.com/api/v1/sms/balance \
  -H "Authorization: Bearer VOTRE_CLE_API"
```

### 3. Accéder à la documentation complète

Consultez la documentation complète sur : **SMS > Documentation API** (`/admin/sms/api/docs`)

## 📊 Endpoints disponibles

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/v1/sms/send` | Envoyer un SMS |
| GET | `/api/v1/sms/history` | Consulter l'historique |
| GET | `/api/v1/sms/balance` | Consulter le solde |

## 🔐 Permissions requises

Pour utiliser l'API, votre compte doit disposer des permissions suivantes :
- `sms.send` - Pour envoyer des SMS
- `sms.history.view` - Pour consulter l'historique
- `sms.stats.view` - Pour consulter les statistiques et le solde

## 💡 Conseils

1. **Sécurité :** Ne partagez jamais votre clé API publiquement
2. **Stockage :** Stockez votre clé dans des variables d'environnement
3. **Rotation :** Régénérez votre clé régulièrement
4. **Monitoring :** Surveillez votre utilisation via les statistiques

## 📞 Support

- Documentation complète : `/admin/sms/api/docs`
- Gestion des clés : `/admin/api-keys`
- Dashboard SMS : `/admin/sms`
