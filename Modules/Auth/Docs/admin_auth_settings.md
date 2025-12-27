# Documentation : Gestion des paramètres de sécurité Auth (Admin)

## Accès à la page de configuration

- Menu : **Administration > Paramètres Auth** (ou `/admin/auth/settings`)
- Permissions requises : `admin.auth.settings.view` (lecture), `admin.auth.settings.edit` (édition)

## Paramètres configurables

- **Max login retries** : Nombre maximum de tentatives de connexion avant blocage temporaire.
- **Lockout period (minutes)** : Durée du blocage temporaire après dépassement des tentatives.
- **Max Lockouts** : Nombre maximal de blocages avant blocage prolongé du compte.
- **Password Reset Retries** : Nombre de tentatives autorisées pour la réinitialisation du mot de passe.
- **IP/Range/User Blacklists** : Liste noire d'IP, de plages IP ou d'utilisateurs (format JSON).

## Utilisation de l'interface

1. Accédez à la page d'administration des paramètres Auth.
2. Modifiez les valeurs souhaitées dans le formulaire.
3. Cliquez sur **Enregistrer tous les paramètres**.
4. Un message de succès ou d'erreur s'affiche en haut de la page.

### Format du champ Blacklist
- Exemple :
```json
{"ips": ["127.0.0.1", "192.168.1."], "users": ["admin"]}
```
- Les IPs peuvent être exactes ou des préfixes (pour plages).
- Les utilisateurs sont identifiés par leur identifiant de connexion.

## Sécurité et validation
- Un token CSRF est requis pour toute modification (géré automatiquement).
- Les valeurs sont validées côté serveur (ex : JSON valide pour la blacklist).
- Toute modification est journalisée via un message flash.

## Bonnes pratiques
- Limitez l'accès à cette page aux administrateurs de confiance.
- Documentez toute modification importante dans un journal d'audit si besoin.
- Testez les paramètres sur un compte de test avant de les appliquer en production.

## Dépannage
- Si un message d'erreur CSRF apparaît, rechargez la page avant de soumettre.
- En cas d'erreur 500, vérifiez le format des champs (notamment la blacklist).
- Pour toute anomalie, consultez les logs dans `storage/logs/`.

---

*Document généré automatiquement – à adapter selon vos besoins spécifiques.*
