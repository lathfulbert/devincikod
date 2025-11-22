# 📁 Système de Gestion de Fichiers

**Date**: 2025-11-22  
**Statut**: ✅ **IMPLÉMENTÉ**

---

## 📋 Présentation

Le module `Core/Files` fournit un système complet pour gérer les uploads, le stockage et la manipulation de fichiers. Il est inspiré de Laravel et CodeIgniter, offrant une API simple et puissante.

### Fonctionnalités Clés

- **Upload Multi-fichiers** : Support natif des formulaires HTML et AJAX.
- **Organisation Automatique** : Sous-dossiers par date (`Y/m/d`) ou utilisateur.
- **Traitement d'Images** : Création automatique de thumbnails, redimensionnement.
- **Sécurité** : Validation MIME, extensions, taille, et middleware de protection.
- **API JSON** : Contrôleur prêt à l'emploi pour les interfaces frontend modernes.
- **Helpers** : Fonctions globales pour une intégration rapide.

---

## ⚙️ Configuration

Le fichier de configuration se trouve dans `Core/Files/Config/files.php`.

```php
return [
    'uploads' => [
        'path' => 'storage/uploads',
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['image/jpeg', 'image/png', 'application/pdf', ...],
    ],
    'images' => [
        'create_thumbnails' => true,
        'thumbnail_dimensions' => ['width' => 150, 'height' => 150],
    ],
    'organize_by_date' => true,
    'organize_by_user' => false,
];
```

---

## 🚀 Utilisation Rapide

### 1. Via Helpers (PHP)

```php
// Upload simple
if ($request->hasFile('avatar')) {
    $result = upload_file($_FILES['avatar'], 'avatars');

    if ($result['success']) {
        $user->avatar = $result['path'];
        $user->save();
    }
}

// Obtenir l'URL
echo file_url($user->avatar);

// Supprimer
delete_file($user->avatar);
```

### 2. Via Service (Injection de Dépendance)

```php
use App\Core\Files\Services\FileManager;

class PostController
{
    protected FileManager $files;

    public function __construct()
    {
        $this->files = new FileManager();
    }

    public function store()
    {
        $results = $this->files->upload($_FILES['gallery'], 'posts');
        // ...
    }
}
```

### 3. Via API AJAX (Frontend)

Routes disponibles (configurées dans `routes/web.php`) :

- `POST /api/files/upload`
- `GET /api/files/list?dir=subfolder`
- `DELETE /api/files/delete`

**Exemple JS :**

```javascript
const formData = new FormData();
formData.append("files[]", fileInput.files[0]);
formData.append("subfolder", "documents");

fetch("/api/files/upload", {
  method: "POST",
  body: formData,
})
  .then((response) => response.json())
  .then((data) => console.log(data));
```

---

## 🔒 Sécurité

### Middleware `secure_upload`

Ce middleware protège les routes d'upload. Il vérifie :

1. Si l'utilisateur est authentifié (optionnel, selon config).
2. Si le dossier de stockage est accessible en écriture.

### Validation

Le service `FileManager` valide automatiquement :

- **Taille** : Rejette les fichiers dépassant `max_size`.
- **Type MIME** : Vérifie le type réel du fichier.
- **Extension** : Vérifie la cohérence extension/MIME.

---

## 📂 Structure du Module

```
Core/Files/
├── Config/
│   └── files.php           # Configuration
├── Contracts/
│   └── FileManagerInterface.php
├── Controllers/
│   └── FileController.php  # API Endpoints
├── Helpers/
│   └── file_helpers.php    # Fonctions globales
├── Middleware/
│   └── SecureUploadMiddleware.php
└── Services/
    └── FileManager.php     # Logique métier
```

---

## 📝 Exemple Complet

Un exemple complet d'interface d'upload (Drag & Drop, barre de progression, liste, suppression) est disponible dans :
`examples/file_upload_demo.php`

---

## 🔧 Extension

Pour ajouter de nouvelles fonctionnalités (ex: support S3), vous pouvez implémenter `FileManagerInterface` et créer un nouveau service, puis modifier le binding dans le conteneur de services.
