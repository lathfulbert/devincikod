# Guide d'Intégration Rapide - Module AI

Ce guide rapide vous permet d'intégrer l'IA dans un nouveau module en moins de 5 minutes.

## Étape 1 : Créer la classe Agent

Créez un fichier `MyModuleAgent.php` dans votre dossier de module (ex: `Modules/MonModule/AI/`).

```php
<?php

namespace Modules\MonModule\AI;

use App\Core\AI\BaseAgent;

class MonModuleAgent extends BaseAgent
{
    // Optionnel : Surcharger le modèle par défaut
    protected $model = 'gpt-3.5-turbo';

    // Ajoutez vos méthodes spécifiques ici
    public function analyzeData($data) {
        return $this->respond("Analyse ces données : " . json_encode($data));
    }
}
```

## Étape 2 : Enregistrer l'Agent

Dans votre fichier principal de module (ex: `Modules/MonModule/MonModule.php`), ajoutez l'enregistrement dans une méthode d'initialisation (ou constructeur si pas de méthode boot).

```php
use App\Core\AI\AIManager;
use Modules\MonModule\AI\MonModuleAgent;

// ...

public function init() // ou boot()
{
    AIManager::getInstance()->registerAgent('MonModule', new MonModuleAgent());
}
```

## Étape 3 : Utiliser l'Agent dans un Contrôleur

```php
use App\Core\AI\AIManager;

public function analyzeAction() {
    $agent = AIManager::getInstance()->getAgent('MonModule');

    if ($agent) {
        $result = $agent->analyzeData(['foo' => 'bar']);
        // Traiter le résultat...
    }
}
```

## C'est tout ! 🚀

Votre module dispose maintenant de capacités d'IA configurables et loggées automatiquement par le système central.
