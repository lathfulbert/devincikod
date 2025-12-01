Implementation Plan - API Key Management

Nouveau Module : Modules\ApiKeys.
Manager APiKey de tout le systeme y compris les modules et tout les endpoints 
Base de données : Table api_keys pour stocker les clés (liées aux utilisateurs).
Interface : Page /admin/api-keys pour lister et générer des clés.
Sécurité : Les clés seront générées aléatoirement (ex: sk_...) et 
affichées une seule fois à l'utilisateur lors de la création pour qu'il puisse les copier.

The goal is to implement a feature to generate, list, and manage API Keys for users, accessible at /admin/api-keys.

Proposed Changes
[NEW] Module: ApiKeys
I will create a new module Modules\ApiKeys to encapsulate this functionality.

[NEW] ApiKeysModule
Register routes:
GET /admin/api-keys (Index)
POST /admin/api-keys (Store/Generate)
DELETE /admin/api-keys/{id} (Revoke)
Register Sidebar Menu: "API Keys" (icon: key)

[NEW] Migration
Table: api_keys
Columns:
	id(PK)
	user_id (FK to users)
	name(string, e.g., "Mobile App")
	key (string, unique, hashed or plain? -> usually hashed in DB, show once to user. 
But for simplicity in this framework context, we might store plain or hash.
Decision: Store plain for now as per "allow copy" request implies retrieval, 
but best practice is show-once. I will implement "Show Once" pattern.)
 
last_used_at (datetime, nullable)
is_active (boolean, default 1)
created_at, updated_at

[NEW] Model
Extends App\Core\Database\Model
Relationships: 
belongsTo(User::class)

[NEW] Controller
index(): List user's API keys.
store(): Generate a new random key (e.g., sk_live_...), save to DB, and flash it to session to show once.
destroy(): Delete/Revoke a key.

[NEW] View
Use backend.layouts.master.
List existing keys (Name, Created At, Last Used, Status, Actions).
"Generate New Key" button.
Important: If a new key is in the session (flashed), display a modal or alert with the key and a "Copy" button.


Verification Plan
	Visit /admin/api-keys.
	Generate a new key.
	Verify the key is shown and can be copied.
	Verify the key appears in the list.
	Verify the key is saved in the database.
	Delete the key and verify it's removed.
