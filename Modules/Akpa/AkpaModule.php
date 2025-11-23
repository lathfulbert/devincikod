<?php

namespace Modules\Akpa;

use App\Core\Module\AbstractModule;

class AkpaModule extends AbstractModule
{
    public function getRoutes(): array
    {
        return [
            ['GET', '/admin/akpa', ['Modules\Akpa\Controllers\AkpaController', 'index']],
            ['GET', '/admin/akpa/create', ['Modules\Akpa\Controllers\AkpaController', 'create']],
            ['POST', '/admin/akpa/store', ['Modules\Akpa\Controllers\AkpaController', 'store']],
            ['GET', '/admin/akpa/{id}/edit', ['Modules\Akpa\Controllers\AkpaController', 'edit']],
            ['POST', '/admin/akpa/{id}/update', ['Modules\Akpa\Controllers\AkpaController', 'update']],
            ['POST', '/admin/akpa/{id}/delete', ['Modules\Akpa\Controllers\AkpaController', 'delete']],
        ];
    }

    protected function getModulePath(): string
    {
        return __DIR__;
    }

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'dropdown',
                'title' => 'Akpa',
                'icon' => 'file-text',
                'children' => [
                    ['title' => 'Articles', 'url' => '/admin/akpa'],
                    ['title' => 'Ajouter', 'url' => '/admin/akpa/create'],
                ]
            ]
        ];
    }
}
