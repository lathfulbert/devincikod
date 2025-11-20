# SunuFramework Walkthrough

## Overview
This is a modular PHP framework built from scratch. It features a central Core and autonomous Modules.

## Directory Structure
- `Core/`: The heart of the framework (Application, Config, Routing, View, Module Manager).
- `Modules/`: Contains independent modules (e.g., `Demo`).
- `templates/`: Global templates (frontend/backend layouts).
- `config/`: Configuration files.
- `public/`: Entry point (`index.php`).

## How to Verify
1.  Ensure your web server (Laragon) points to `c:/laragon/www/sunuframework2/public`.
2.  Open your browser and navigate to:
    - `http://sunuframework2.test/demo` (or your local equivalent).
    - `http://sunuframework2.test/blog` to see the new Blog module.
    - `http://sunuframework2.test/login` to test the Auth module (User: admin, Pass: password).
    - `http://sunuframework2.test/login` to test the Auth module (User: admin, Pass: password).
3.  You should see the "Welcome to the Demo Module" message or the list of blog posts.

## Database & Migrations
1.  Configure your database in `.env` (copy from `.env.example` if needed).
2.  Run migrations: `php sunu migrate`.
3.  This will create the `users` table defined in `Modules/Auth`.

## CLI
- Use `php sunu migrate` to run pending migrations.

## Adding a New Module
1.  Create a new directory in `Modules/` (e.g., `Modules/Blog`).
2.  Create a class `Modules\Blog\BlogModule` implementing `App\Core\Module\ModuleContract`.
3.  Define your routes in `getRoutes()`.
4.  Create views in `templates/blog/` (or inject them).
5.  The module is automatically discovered by `ModuleManager`.

## Key Features
- **PSR-4 Autoloading**: Configured in `composer.json`.
- **Module System**: `ModuleManager` scans and boots modules.
- **Routing**: Simple `Router` class handling GET/POST.
- **Views**: `View` class handling layouts and content injection.
