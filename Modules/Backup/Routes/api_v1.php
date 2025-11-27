<?php

use App\Core\Routing\Route;

// API V1 Routes
Route::group(['prefix' => 'api/v1/backups', 'middleware' => ['api', 'auth:api']], function () {
    Route::get('/', [\Modules\Backup\Controllers\Api\BackupController::class, 'index']);
    Route::post('/run', [\Modules\Backup\Controllers\Api\BackupController::class, 'run']);
    Route::get('/{id}', [\Modules\Backup\Controllers\Api\BackupController::class, 'show']);
    Route::delete('/{id}', [\Modules\Backup\Controllers\Api\BackupController::class, 'delete']);

    // Monitoring
    Route::get('/system/health', [\Modules\Backup\Controllers\Api\MonitoringController::class, 'health']);
    Route::get('/system/stats', [\Modules\Backup\Controllers\Api\MonitoringController::class, 'stats']);
});
