<?php

namespace Modules\SmsCore;

use App\Core\Module\AbstractModule;

class SmsCoreModule extends AbstractModule
{
    protected function getModulePath(): string
    {
        return __DIR__;
    }

    public function getRoutes(): array
    {
        // Load routes from Routes/web.php file
        return [
            'web' => __DIR__ . '/Routes/web.php',
            'admin' => __DIR__ . '/Routes/admin.php', // For backward compatibility with /admin/sms routes
        ];
    }

    public function boot(): void
    {
        // Enregistrer les widgets du module
        if (function_exists('register_widget')) {
            register_widget('sms_core', \Modules\SmsCore\Widgets\SmsStatisticsWidget::class);
        }
    }
}
