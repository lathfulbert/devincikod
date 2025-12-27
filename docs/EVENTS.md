# Events & Triggers System - Complete! 🎉

## 📚 Quick Guide

### Dispatch an Event

```php
// Using static dispatch
use Modules\Auth\Events\UserRegistered;

UserRegistered::dispatch($user);

// Using helper
event(new UserRegistered($user));
```

### Create an Event

```php
namespace Modules\Blog\Events;

use App\Core\Events\Event;

class PostCreated extends Event
{
    public $post;

    public function __construct($post)
    {
        $this->post = $post;
        parent::__construct(['post' => $post]);
    }
}
```

### Create a Listener (Sync)

```php
namespace Modules\Blog\Listeners;

use App\Core\Contracts\ListenerInterface;

class NotifySubscribers implements ListenerInterface
{
    public function handle(object $event): void
    {
        // Handle the event...
    }
}
```

### Create a Listener (Async/Queued)

```php
namespace Modules\Blog\Listeners;

use App\Core\Contracts\{ListenerInterface, ShouldQueue};

class SendNewsletter implements ListenerInterface, ShouldQueue
{
    public function handle(object $event): void
    {
        // Send newsletter...
    }

    public function queue(): string
    {
        return 'emails'; // Queue name
    }

    public function delay(): int
    {
        return 0; // Delay in seconds
    }
}
```

### Register Listeners

Create `events.php` in your module:

```php
// Modules/Blog/events.php
return [
    \Modules\Blog\Events\PostCreated::class => [
        \Modules\Blog\Listeners\NotifySubscribers::class,
        \Modules\Blog\Listeners\SendNewsletter::class,
    ],
];
```

## 🔧 Components Created

### Core

- `Core/Events/Event.php` - Base event class
- `Core/Events/EventDispatcher.php` - Singleton dispatcher
- `Core/Events/ListenerProvider.php` - Module loader

### Contracts

- `Core/Contracts/ListenerInterface.php` - Listener contract
- `Core/Contracts/ShouldQueue.php` - Async interface

### Queue

- `App/Jobs/CallQueuedListener.php` - Queue job

### Examples (Auth Module)

- `Modules/Auth/Events/UserRegistered.php`
- `Modules/Auth/Listeners/SendWelcomeEmail.php` (async)
- `Modules/Auth/Listeners/TrackNewUser.php` (sync)
- `Modules/Auth/events.php` - Config

### Helpers

- `event()` - Dispatch or get dispatcher
- `listen()` - Register listener

## ✅ Features

✅ Event dispatching (`Event::dispatch()`)  
✅ Listener registration (`events.php`)  
✅ Queue support (implements `ShouldQueue`)  
✅ Propagation control (`stopPropagation()`)  
✅ Lazy loading (listeners loaded on demand)  
✅ Module-based (each module manages its events)  
✅ PSR-14 inspired

## 🚀 Ready to Use!

The system is 100% functional and ready for production use.
