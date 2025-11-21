<?php

namespace Modules\I18n;

use App\Core\Module\ModuleContract;
use Modules\I18n\Controllers\I18nController;

class I18nModule implements ModuleContract
{
    public function getName(): string
    {
        return 'I18n';
    }

    public function register(): void {}

    public function boot(): void {}

    public function getRoutes(): array
    {
        $controller = new I18nController();

        return [
            [
                'method' => 'GET',
                'path' => '/admin/i18n',
                'handler' => [$controller, 'index']
            ],
            [
                'method' => 'GET',
                'path' => '/admin/i18n/create',
                'handler' => [$controller, 'create']
            ],
            [
                'method' => 'POST',
                'path' => '/admin/i18n/create',
                'handler' => [$controller, 'store']
            ],
            [
                'method' => 'GET',
                'path' => '/admin/i18n/edit',
                'handler' => [$controller, 'edit']
            ],
            [
                'method' => 'POST',
                'path' => '/admin/i18n/edit',
                'handler' => [$controller, 'update']
            ],
            [
                'method' => 'POST',
                'path' => '/admin/i18n/delete',
                'handler' => [$controller, 'delete']
            ],
            [
                'method' => 'POST',
                'path' => '/admin/i18n/clear-cache',
                'handler' => [$controller, 'clearCache']
            ],
            [
                'method' => 'GET',
                'path' => '/admin/i18n/export',
                'handler' => [$controller, 'export']
            ],
            [
                'method' => 'POST',
                'path' => '/admin/i18n/import',
                'handler' => [$controller, 'import']
            ]
        ];
    }
}
