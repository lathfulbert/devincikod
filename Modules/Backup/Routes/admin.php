<?php

use App\Core\Routing\Route;

// Admin Routes
Route::group(['prefix' => 'admin/backups', 'middleware' => ['auth', 'admin']], function () {
    Route::get('/', 'Modules\Backup\Controllers\Admin\BackupController@index')->name('admin.backups.index');
    Route::post('/create', 'Modules\Backup\Controllers\Admin\BackupController@create')->name('admin.backups.create');
    Route::get('/download/{id}', 'Modules\Backup\Controllers\Admin\BackupController@download')->name('admin.backups.download');
    Route::post('/restore/{id}', 'Modules\Backup\Controllers\Admin\BackupController@restore')->name('admin.backups.restore');
    Route::delete('/delete/{id}', 'Modules\Backup\Controllers\Admin\BackupController@delete')->name('admin.backups.delete');

    // Monitoring
    Route::get('/health', 'Modules\Backup\Controllers\Admin\MonitoringController@index')->name('admin.backups.health');
    Route::get('/stats', 'Modules\Backup\Controllers\Admin\MonitoringController@stats')->name('admin.backups.stats');
});
