<?php
// Route pour la gestion des widgets du dashboard
use Modules\Dashboard\Controllers\WidgetManagerController;

return [
    ['GET', '/admin/dashboard/widgets', [WidgetManagerController::class, 'index'], ['middleware' => ['auth', 'can:access.dashboard']]],
    ['POST', '/admin/dashboard/widgets/toggle', [WidgetManagerController::class, 'toggle'], ['middleware' => ['auth', 'can:access.dashboard']]],
];
