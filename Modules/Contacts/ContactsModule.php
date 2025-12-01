<?php

namespace Modules\Contacts;

use App\Core\Module\AbstractModule;

class ContactsModule extends AbstractModule
{
    protected function getModulePath(): string
    {
        return __DIR__;
    }

    public function getRoutes(): array
    {
        $authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

        return [
            // Contacts routes
            ['GET', '/admin/contacts', [\Modules\Contacts\Controllers\ContactController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/contacts/create', [\Modules\Contacts\Controllers\ContactController::class, 'create'], [$authMiddleware]],
            ['POST', '/admin/contacts/store', [\Modules\Contacts\Controllers\ContactController::class, 'store'], [$authMiddleware]],
            ['GET', '/admin/contacts/{id}/edit', [\Modules\Contacts\Controllers\ContactController::class, 'edit'], [$authMiddleware]],
            ['POST', '/admin/contacts/{id}/update', [\Modules\Contacts\Controllers\ContactController::class, 'update'], [$authMiddleware]],
            ['GET', '/admin/contacts/{id}/delete', [\Modules\Contacts\Controllers\ContactController::class, 'delete'], [$authMiddleware]],

            // Custom fields routes
            ['GET', '/admin/contacts/fields', [\Modules\Contacts\Controllers\CustomFieldController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/contacts/fields/create', [\Modules\Contacts\Controllers\CustomFieldController::class, 'create'], [$authMiddleware]],
            ['POST', '/admin/contacts/fields/store', [\Modules\Contacts\Controllers\CustomFieldController::class, 'store'], [$authMiddleware]],
            ['GET', '/admin/contacts/fields/{id}/edit', [\Modules\Contacts\Controllers\CustomFieldController::class, 'edit'], [$authMiddleware]],
            ['POST', '/admin/contacts/fields/{id}/update', [\Modules\Contacts\Controllers\CustomFieldController::class, 'update'], [$authMiddleware]],
            ['GET', '/admin/contacts/fields/{id}/delete', [\Modules\Contacts\Controllers\CustomFieldController::class, 'delete'], [$authMiddleware]],
        ];
    }

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'dropdown',
                'title' => 'Contacts',
                'icon' => 'users',
                'children' => [
                    ['title' => 'Tous les contacts', 'url' => '/admin/contacts'],
                    ['title' => 'Nouveau contact', 'url' => '/admin/contacts/create'],
                    ['title' => 'Champs personnalisés', 'url' => '/admin/contacts/fields'],
                ]
            ]
        ];
    }
}
