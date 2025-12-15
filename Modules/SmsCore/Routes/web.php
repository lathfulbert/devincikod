<?php

use Modules\SmsCore\Controllers\SenderNameController;
use Modules\SmsCore\Controllers\SmsController;
use Modules\SmsCore\Controllers\SmsApiController;

/** @var \App\Core\Routing\Router $router */

// Sender Names Management (Admin only)
// Changement du préfixe pour accès via /admin/sms/sender-names
$router->group([
    'prefix' => '/admin/sms/sender-names',
    'middleware' => ['auth', 'module_access:sms_core']
], function ($router) {
    // List sender names
    $router->get('', [SenderNameController::class, 'index'])
        ->middleware('can:sms.sender_names.view')
        ->name('sms.sender_names.index');

    // Create sender name
    $router->get('/create', [SenderNameController::class, 'create'])
        ->middleware('can:sms.sender_names.manage')
        ->name('sms.sender_names.create');

    $router->post('/store', [SenderNameController::class, 'store'])
        ->middleware('can:sms.sender_names.manage')
        ->name('sms.sender_names.store');

    // Edit sender name
    $router->get('/edit', [SenderNameController::class, 'edit'])
        ->middleware('can:sms.sender_names.manage')
        ->name('sms.sender_names.edit');

    $router->post('/update', [SenderNameController::class, 'update'])
        ->middleware('can:sms.sender_names.manage')
        ->name('sms.sender_names.update');

    // Delete sender name
    $router->post('/delete', [SenderNameController::class, 'delete'])
        ->middleware('can:sms.sender_names.manage')
        ->name('sms.sender_names.delete');

    // Assign users to sender name
    $router->get('/assign-users', [SenderNameController::class, 'assignUsers'])
        ->middleware('can:sms.sender_names.assign')
        ->name('sms.sender_names.assign_users');

    $router->post('/save-assignments', [SenderNameController::class, 'saveAssignments'])
        ->middleware('can:sms.sender_names.assign')
        ->name('sms.sender_names.save_assignments');

    // Bulk assign to user
    $router->post('/bulk-assign-to-user', [SenderNameController::class, 'bulkAssignToUser'])
        ->middleware('can:sms.sender_names.assign')
        ->name('sms.sender_names.bulk_assign');
});

// API endpoint for getting user's sender names
$router->get('/api/sms/sender-names/user', [SenderNameController::class, 'apiGetUserSenderNames'])
    ->middleware('auth')
    ->middleware('module_access:sms_core')
    ->name('api.sms.sender_names.user');

// SMS Sending routes (if not already defined)
$router->group([
    'prefix' => '/admin/sms',
    'middleware' => ['auth', 'module_access:sms_core']
], function ($router) {
    // Single SMS
    $router->get('/send', [SmsController::class, 'sendForm'])
        ->middleware('can:sms.send')
        ->name('sms.send.form');

    $router->post('/send', [SmsController::class, 'send'])
        ->middleware('can:sms.send')
        ->name('sms.send');

    // Bulk SMS
    $router->get('/bulk', [SmsController::class, 'bulkForm'])
        ->middleware('can:sms.bulk')
        ->name('sms.bulk.form');

    $router->post('/bulk', [SmsController::class, 'bulkSend'])
        ->middleware('can:sms.bulk')
        ->name('sms.bulk');

    // Parse file for column detection (used by import with variables)
    $router->post('/parse-file', [SmsController::class, 'parseFile'])
        ->middleware('can:sms.send')
        ->name('sms.parse_file');

    // Campaign management
    $router->get('/campaigns', [SmsController::class, 'campaigns'])
        ->middleware('can:sms.campaigns.view')
        ->name('sms.campaigns');

    $router->post('/campaigns/create', [SmsController::class, 'createCampaign'])
        ->middleware('can:sms.campaigns.create')
        ->name('sms.campaigns.create');
});
