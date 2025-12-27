# Module AI pour le Core Framework LathDevinci

## Objectif
Créer un module AI intégré au Core du framework, capable d’interagir avec tous les modules internes et futurs de manière transparente. Le module doit être extensible, modulable et configurable, avec la possibilité de changer de modèle AI sans impacter le fonctionnement global.

---

## 1. Architecture du module
- Classe centrale : `AIManager`
- API interne pour exposer les fonctionnalités aux autres modules
- Gestion dynamique des modèles AI
- Historique des interactions, logs et statistiques pour chaque agent
- Interface pour enregistrer et gérer des agents AI spécifiques à chaque module
- Intégration native de l'API OpenAI avec configuration par défaut

### Exemple de structure de classes
```php
use OpenAI\OpenAI;

class AIManager {
    private static $instance;
    private $agents = [];
    private $openAIClient;

    private function __construct() {
        $this->openAIClient = new OpenAI(getenv('OPENAI_API_KEY'));
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new AIManager();
        }
        return self::$instance;
    }

    public function registerAgent(string $module, AIInterface $agent) {
        $this->agents[$module] = $agent;
    }

    public function getAgent(string $module): ?AIInterface {
        return $this->agents[$module] ?? null;
    }

    public function getOpenAIClient(): OpenAI {
        return $this->openAIClient;
    }
}

interface AIInterface {
    public function respond(string $input): string;
    public function setModel(string $model);
    public function configure(array $params);
}
```

---

## 2. Fonctionnalités principales
- Interaction inter-modules
- Extensible et modulable
- Paramétrable depuis le backend
  - Choix du modèle AI pour chaque agent (OpenAI par défaut)
  - Paramètres de configuration (temperature, max tokens, mode interactif)
  - Activer/désactiver des agents
- Diagramme des modèles configurables

---

## 3. Backend et interface
- Gestion complète depuis le backend :
  - Vue des agents AI actifs et leurs modules associés
  - Configuration des modèles AI
  - Historique des interactions et logs
- API interne pour les modules :
  - Envoyer des requêtes à l’agent AI
  - Recevoir des réponses et suggestions
  - Recevoir des événements en temps réel (webhooks internes)

---

## 4. Exigences techniques
- Indépendant mais intégré au Core
- Pattern singleton ou manager global
- Compatible avec futures mises à jour
- Documentation interne claire pour l’extension
- Intégration directe de l'API OpenAI avec configuration par défaut pour démarrer rapidement

---

## 5. Exemple d’utilisation
```php
// Enregistrer un agent pour le module Blog
$blogAgent = new BlogAI();
AIManager::getInstance()->registerAgent('Blog', $blogAgent);

// Utiliser l'agent pour générer un résumé avec OpenAI par défaut
$agent = AIManager::getInstance()->getAgent('Blog');
$response = $agent->respond('Résumé de l'article : ...');

// Utiliser directement le client OpenAI
$client = AIManager::getInstance()->getOpenAIClient();
$result = $client->chat()->create([
    'model' => 'gpt-4',
    'messages' => [[ 'role' => 'user', 'content' => 'Génère un résumé de cet article.' ]]
]);
```

---

## 6. Bonus avancé
- Support multi-backends AI (OpenAI, HuggingFace, modèles propriétaires)
- Interface unifiée pour changer de fournisseur AI sans impacter le fonctionnement
- Système de "skills" ou plugins AI pour capacités spécifiques à chaque module

---

## Diagramme conceptuel
```
+----------------+
|   AIManager    |
+----------------+
| - agents[]     |
| - openAIClient |
+----------------+
| +registerAgent |
| +getAgent      |
| +getOpenAIClient|
+----------------+
        |
        v
+----------------+       +----------------+
|  BlogAI Agent  |       | CRM AI Agent   |
+----------------+       +----------------+
| +respond()     |       | +respond()     |
| +setModel()    |       | +setModel()    |
+----------------+       +----------------+
```

---

## Notes
- Le module est conçu pour s’étendre avec chaque nouveau module intégré dans le framework.
- Chaque agent AI peut être personnalisé et ajusté depuis le backend.
- Les logs et statistiques permettent un suivi précis de l’usage et de la performance de chaque agent.
- OpenAI est intégré par défaut pour démarrer rapidement, avec possibilité de changer de modèle à tout moment.
