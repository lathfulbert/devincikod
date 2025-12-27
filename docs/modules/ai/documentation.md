# Documentation du Module AI - SunuFramework2

## Introduction

Le module AI est une intégration native au cœur du framework SunuFramework2, conçue pour faciliter l'interaction avec des modèles d'intelligence artificielle (comme OpenAI GPT) de manière modulaire et extensible. Il permet à chaque module du framework (Blog, CRM, etc.) de disposer de son propre "Agent AI" configuré spécifiquement pour ses besoins.

---

## Architecture

Le système repose sur trois composants principaux situés dans `Core/AI` :

1.  **`AIManager`** : Un singleton qui gère l'instance du client OpenAI et le registre des agents.
2.  **`AIInterface`** : L'interface que tous les agents doivent implémenter.
3.  **`BaseAgent`** : Une classe abstraite fournissant une implémentation par défaut pour communiquer avec OpenAI.

---

## Installation & Configuration

### Prérequis

Le module nécessite la librairie `openai-php/client`. Si elle n'est pas installée :

```bash
composer require openai-php/client
```

### Configuration

Ajoutez votre clé API OpenAI dans le fichier `.env` à la racine du projet :

```env
OPENAI_API_KEY=sk-votre-cle-api-ici...
```

Vous pouvez également configurer le modèle par défaut via l'interface d'administration dans **AI Module > Settings**.

---

## Utilisation

### 1. Utilisation Basique (Client Direct)

Vous pouvez accéder directement au client OpenAI n'importe où dans votre code :

```php
use App\Core\AI\AIManager;

$manager = AIManager::getInstance();
$client = $manager->getOpenAIClient();

$response = $client->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'user', 'content' => 'Bonjour !'],
    ],
]);

echo $response->choices[0]->message->content;
```

### 2. Création d'un Agent Personnalisé

Pour créer un agent spécifique à un module (ex: Blog), créez une classe qui étend `BaseAgent` :

```php
namespace Modules\Blog\AI;

use App\Core\AI\BaseAgent;

class BlogAgent extends BaseAgent {

    public function __construct() {
        parent::__construct();
        // Configuration spécifique
        $this->model = 'gpt-4';
        $this->configure(['temperature' => 0.7]);
    }

    /**
     * Exemple de méthode métier
     */
    public function generateSummary($articleContent) {
        return $this->respond("Génère un résumé court pour cet article : " . $articleContent);
    }
}
```

### 3. Enregistrement de l'Agent

Enregistrez votre agent, idéalement dans le fichier principal de votre module (`Module.php`) ou un ServiceProvider :

```php
use App\Core\AI\AIManager;
use Modules\Blog\AI\BlogAgent;

// ...
public function boot() {
    AIManager::getInstance()->registerAgent('Blog', new BlogAgent());
}
```

### 4. Récupération et Utilisation de l'Agent

```php
$agent = AIManager::getInstance()->getAgent('Blog');

if ($agent) {
    $summary = $agent->generateSummary($content);
}
```

---

## Gestion via le Backend

Le module AI fournit une interface d'administration complète accessible via le menu **AI Module** :

- **Dashboard** : Vue d'ensemble des agents enregistrés et de leur configuration.
- **Test AI** : Une interface "Playground" pour tester vos prompts directement depuis le back-office.
- **Logs** : Historique des interactions (requêtes, réponses, tokens utilisés) pour le débogage et le suivi des coûts.
- **Settings** : Configuration globale (Clé API, Modèle par défaut).

---

## Base de Données

Le module utilise deux tables principales :

- `ai_agents` : Stocke la configuration persistante des agents (activé/désactivé, paramètres spécifiques).
- `ai_logs` : Enregistre l'historique des appels API.

---

## Extension Future

Le système est conçu pour être agnostique au fournisseur. Bien que configuré par défaut pour OpenAI, `AIInterface` permet d'implémenter des adaptateurs pour d'autres fournisseurs (HuggingFace, Anthropic, Local LLMs) sans changer le code métier des modules.
