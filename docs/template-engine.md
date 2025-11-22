# Template Engine & Route Helpers Documentation

## Table des matières
1. [Introduction](#introduction)  
2. [Template Engine](#template-engine)  
   - [Directives d’Héritage](#directives-dhéritage)  
   - [Includes](#includes)  
   - [Conditionnels](#conditionnels)  
   - [Boucles](#boucles)  
   - [Échappement & affichage](#échappement--affichage)  
   - [Helpers natifs disponibles dans les vues](#helpers-natifs-disponibles-dans-les-vues)  
   - [Cache des templates](#cache-des-templates)  
3. [Helpers Routes & Navigation](#helpers-routes--navigation)  
   - [Route::currentRouteName()](#routecurrentroutename)  
   - [route('name', params)](#routename-params)  
   - [is_active_route('name')](#is_active_routename)  
   - [url()](#url)  
   - [Named routes map](#named-routes-map)  
4. [Architecture & Classes](#architecture--classes)  
5. [Exemples d’utilisation](#exemples-dutilisation)  
6. [Bonus](#bonus)

---

## Introduction
Cette documentation décrit un **Template Engine avancé** pour un framework PHP ou NodeJS, inspiré de Blade et Twig, intégrant toutes les directives essentielles (`extends`, `include`, `section`, `yield`) et des **helpers de gestion des routes** comme `route()`, `Route::currentRouteName()`, `url()`, etc.

---

## Template Engine

### Directives d’Héritage
- `@extends('layout.master')` : Déclare le layout parent  
- `@section('title') ... @endsection` : Définit une section de contenu  
- `@yield('content')` : Affiche le contenu d’une section dans le layout  
- Gestion multiple d’héritage et surcharge des sections  

### Includes
- `@include('partials.header')` : Inclut un fichier partiel  
- `@include('components.card', ['title' => 'Test'])` : Include avec variables  

### Conditionnels
- `@if(condition) ... @elseif(condition) ... @else ... @endif`  
- `@unless(condition) ... @endunless`  
- `@isset($var) ... @endisset`  
- `@empty($var) ... @endempty`  

### Boucles
- `@foreach($items as $item) ... @endforeach`  
- `@for($i=0; $i<10; $i++) ... @endfor`  
- `@while(condition) ... @endwhile`  

### Échappement & affichage
- `{{ variable }}` : Échappement HTML sécurisé  
- `{!! html !!}` : Affichage brut  

### Helpers natifs disponibles dans les vues
- `route('name', [params])` : Génère une URL nommée  
- `url('path')` : URL absolue  
- `asset('path/to/file')` : URL vers un asset public  
- `csrf()` : Token CSRF pour formulaires  
- `old('field')` : Valeur précédente du formulaire  
- `config('app.name')` : Accès aux paramètres de config  
- `trans('message.key')` : Traductions  
- `dump()`, `dd()` : Debug  

### Cache des templates
- Compilation dans `/storage/cache/views`  
- Hashing intelligent et invalidation automatique  
- Optimisation de performance via compilation PHP  

---

## Helpers Routes & Navigation

### Route::currentRouteName()
Retourne le nom de la route en cours. Utilisable dans les vues et contrôleurs :

```php
$current = Route::currentRouteName();
```

### route('name', params)
Génère l’URL d’une route nommée avec paramètres :

```php
route('profile.show', ['id' => 10]); // /profile/10
```

### is_active_route('name')
Helper pour menu actif :

```php
<li class="{{ is_active_route('dashboard.index') }}">Dashboard</li>
```

### url()
Retourne l’URL absolue d’un chemin :

```php
url('/login'); // https://mon-site.com/login
```

### Named routes map
Tableau interne des routes nommées :

```php
[
    'dashboard.index' => '/dashboard',
    'profile.show' => '/profile/{id}',
]
```

---

## Architecture & Classes

### Classes principales
- **TemplateEngine** : Parse les vues et gère directives  
- **Compiler** : Compile les templates en PHP  
- **DirectiveManager** : Gère toutes les directives personnalisées  
- **View** : Représente une vue et ses données  
- **ViewServiceProvider** : Injection des helpers dans les vues  
- **RouteHelper / UrlGenerator** : Gestion des routes et génération d’URL  

### Diagramme simplifié (pseudo)
```
ViewServiceProvider
     |
     v
 TemplateEngine <---- Compiler
     |
     v
   Views
     |
     v
DirectiveManager
```

---

## Exemples d’utilisation

### Layout parent
```php
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
</head>
<body>
    @include('partials.nav')
    <div class="content">
        @yield('content')
    </div>
</body>
</html>
```

### Vue enfant
```php
@extends('layout.master')

@section('title', 'Dashboard')

@section('content')
    <h1>{{ trans('dashboard.welcome') }}</h1>

    @include('components.card', ['title' => 'Profil', 'link' => route('profile.show', ['id'=>1])])

    @if(auth()->check())
        <p>Hello {{ auth()->user()->name }}</p>
    @endif
@endsection
```

---

## Bonus

### Directives personnalisées
```php
@datetime($date) 
// génère <span>{{ $date->format('d/m/Y') }}</span>
```

### Components
```php
<x-alert type="success">Operation réussie</x-alert>
```

---

**Fin de la documentation `template-engine.md`**  
