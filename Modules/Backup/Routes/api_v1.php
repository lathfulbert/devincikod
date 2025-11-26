<?php

use App\Core\Routing\Route;

// API V1 Routes
Route::group(['prefix' => 'api/v1/backups', 'middleware' => ['api', 'auth:api']], function () {
    Route::get('/', 'Modules\Backup\Controllers\Api\BackupController@index');
    Route::post('/run', 'Modules\Backup\Controllers\Api\BackupController@run');
    Route::get('/{id}', 'Modules\Backup\Controllers\Api\BackupController@show');
    Route::delete('/{id}', 'Modules\Backup\Controllers\Api\BackupController@delete');

    // Monitoring
    Route::get('/system/health', 'Modules\Backup\Controllers\Api\MonitoringController@health');
    Route::get('/system/stats', 'Modules\Backup\Controllers\Api\MonitoringController@stats');
});
