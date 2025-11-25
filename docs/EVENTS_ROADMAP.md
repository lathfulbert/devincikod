# Events & Triggers - Roadmap d'Améliorations Futures

## 🚀 Améliorations Prioritaires

### 1. **Event Priority (Priorité des Listeners)**

Permettre de définir un ordre d'exécution des listeners.

```php
// Dans events.php
return [
    UserRegistered::class => [
        'listeners' => [
            CheckUserBanned::class => 100,      // Haute priorité
            SendWelcomeEmail::class => 50,      // Moyenne
            TrackNewUser::class => 10,          // Basse
        ]
    ],
];
```

**Implémentation:** Ajouter un système de tri par priorité dans `ListenerProvider`.

---

### 2. **Wildcard Event Listeners**

Écouter plusieurs events avec des patterns.

```php
// Écouter tous les events User*
listen('User*', LogUserActivity::class);

// Écouter tous les events
listen('*', AuditLogger::class);
```

**Cas d'usage:** Logging global, audit trail, analytics.

---

### 3. **Conditional Listeners**

N'exécuter un listener que si certaines conditions sont remplies.

```php
class SendPromoEmail implements ListenerInterface, ConditionalListener
{
    public function shouldHandle(object $event): bool
    {
        return $event->user->subscription === 'premium';
    }

    public function handle(object $event): void
    {
        // Send promo...
    }
}
```

---

### 4. **Event Replay & Debugging**

Stocker les events pour replay et debug.

```php
// Activer le mode debug
EventDispatcher::enableDebug();

// Voir l'historique
$history = EventDispatcher::getHistory();

// Rejouer un event
EventDispatcher::replay($eventId);
```

**Stockage:** Base de données ou fichiers logs structurés.

---

### 5. **Redis Pub/Sub (Distributed Events)**

Distribuer les events entre plusieurs serveurs.

```php
// Publier sur tous les serveurs
UserRegistered::broadcast($user);

// Configuration
'events' => [
    'driver' => 'redis',
    'connection' => 'default',
    'channel' => 'app-events',
]
```

**Cas d'usage:** Architecture microservices, scalabilité horizontale.

---

## 🔧 Améliorations Techniques

### 6. **Lazy Event Registration**

Charger les events seulement quand nécessaire.

```php
// Au lieu de charger tous les events au boot
// Charger à la demande lors du premier dispatch
```

---

### 7. **Event Versioning**

Gérer plusieurs versions d'un même event.

```php
UserRegistered::v1()->dispatch($user);
UserRegistered::v2()->dispatch($user);
```

---

### 8. **Event Middleware**

Transformer ou filtrer les events avant dispatch.

```php
EventDispatcher::middleware([
    SanitizeEventData::class,
    LogEventDispatch::class,
]);
```

---

### 9. **Typed Events**

Validation stricte des données d'event.

```php
class UserRegistered extends Event
{
    public function __construct(
        public readonly User $user,
        public readonly string $source,
        public readonly \DateTime $timestamp
    ) {}
}
```

---

### 10. **Event Batching**

Regrouper plusieurs events pour exécution batch.

```php
EventDispatcher::batch([
    new UserRegistered($user1),
    new UserRegistered($user2),
    new UserRegistered($user3),
]);
```

---

## 📊 Monitoring & Analytics

### 11. **Event Metrics**

Tracking des performances et statistiques.

```php
EventMonitor::getStats('UserRegistered');
// {
//   total_dispatched: 1523,
//   avg_duration_ms: 45,
//   listeners_count: 3,
//   failed_count: 2
// }
```

---

### 12. **Event Dashboard (Backoffice)**

Interface admin pour gérer les events.

**Features:**

- Liste des events disponibles
- Listeners actifs/inactifs par event
- Historique des dispatches
- Statistiques temps réel
- Test manuel d'events

---

## 🔐 Sécurité & Contrôle

### 13. **Event Authorization**

Contrôler qui peut dispatcher certains events.

```php
if (Gate::allows('dispatch', UserDeleted::class)) {
    UserDeleted::dispatch($user);
}
```

---

### 14. **Rate Limiting**

Limiter le nombre de dispatches par period.

```php
class SpamProtection implements ListenerInterface
{
    public function handle(object $event): void
    {
        RateLimiter::for('user-events')
            ->limit(10)
            ->perMinute();
    }
}
```

---

## 🧪 Testing & Quality

### 15. **Event Faking**

Tester sans exécuter les listeners.

```php
Event::fake([UserRegistered::class]);

// Execute code...

Event::assertDispatched(UserRegistered::class, function ($event) {
    return $event->user->id === 1;
});
```

---

### 16. **Event Mocking**

Mocker les events dans les tests.

```php
$mock = Mockery::mock(UserRegistered::class);
EventDispatcher::shouldReceive('dispatch')
    ->once()
    ->with($mock);
```

---

## 🎨 Developer Experience

### 17. **Event Generator CLI**

Commande pour générer events et listeners.

```bash
php sunu make:event UserRegistered
php sunu make:listener SendWelcomeEmail --event=UserRegistered
```

---

### 18. **Event Documentation Auto**

Générer documentation depuis le code.

```bash
php sunu events:doc
# Génère docs/events.md avec tous les events et leurs listeners
```

---

### 19. **Event Visualization**

Graphe visuel des events et listeners.

```bash
php sunu events:graph
# Génère un graphe Mermaid ou GraphViz
```

---

## 🔄 Advanced Patterns

### 20. **Event Sourcing**

Stocker tous les events comme source de vérité.

```php
class UserAggregate
{
    private array $events = [];

    public function register(array $data)
    {
        $event = new UserRegistered($data);
        $this->recordEvent($event);
        return $this;
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}
```

---

### 21. **CQRS Integration**

Séparer commandes et queries via events.

```php
CommandBus::dispatch(new RegisterUserCommand($data));
// → Déclenche UserRegistered event
// → Projections mises à jour
QueryBus::execute(new GetUserQuery($id));
```

---

### 22. **Saga Pattern**

Gérer les transactions distribuées.

```php
class OrderSaga
{
    public function handle(OrderPlaced $event)
    {
        PaymentProcessed::listen(fn($e) => $this->shipOrder($e));
        PaymentFailed::listen(fn($e) => $this->cancelOrder($e));
    }
}
```

---

## 📝 Priorisation Recommandée

### Phase 1 (Court terme - 1-2 semaines)

1. Event Priority
2. Event Dashboard (Backoffice)
3. Event Generator CLI

### Phase 2 (Moyen terme - 1 mois)

4. Wildcard Listeners
5. Event Metrics & Monitoring
6. Conditional Listeners

### Phase 3 (Long terme - 2-3 mois)

7. Redis Pub/Sub
8. Event Replay & Debugging
9. Event Faking (Testing)

### Phase 4 (Avancé - 3+ mois)

10. Event Sourcing
11. CQRS Integration
12. Saga Pattern

---

## 🎯 Impacts Estimés

| Amélioration   | Complexité   | Impact Business | Impact Technique |
| -------------- | ------------ | --------------- | ---------------- |
| Event Priority | 🟡 Moyenne   | ⭐⭐⭐          | ⭐⭐⭐           |
| Dashboard      | 🟡 Moyenne   | ⭐⭐⭐⭐⭐      | ⭐⭐⭐           |
| CLI Generator  | 🟢 Facile    | ⭐⭐⭐⭐        | ⭐⭐⭐⭐         |
| Redis Pub/Sub  | 🔴 Difficile | ⭐⭐⭐⭐⭐      | ⭐⭐⭐⭐⭐       |
| Event Sourcing | 🔴 Difficile | ⭐⭐⭐⭐        | ⭐⭐⭐⭐⭐       |

**Légende:**

- 🟢 Facile (< 1 jour)
- 🟡 Moyenne (1-3 jours)
- 🔴 Difficile (> 1 semaine)

---

**🚀 Next Steps:** Choisissez 2-3 améliorations prioritaires et créez un plan d'implémentation !
