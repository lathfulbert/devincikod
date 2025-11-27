<?php

use App\Core\Routing\Route;

// Admin Routes
Route::group(['prefix' => 'admin/backups', 'middleware' => ['auth', 'admin']], function () {
    Route::get('/', [\Modules\Backup\Controllers\Admin\BackupController::class, 'index'])->name('admin.backups.index');
    Route::post('/create', [\Modules\Backup\Controllers\Admin\BackupController::class, 'create'])->name('admin.backups.create');
    Route::get('/download/{id}', [\Modules\Backup\Controllers\Admin\BackupController::class, 'download'])->name('admin.backups.download');
    Route::post('/restore/{id}', [\Modules\Backup\Controllers\Admin\BackupController::class, 'restore'])->name('admin.backups.restore');
    Route::delete('/delete/{id}', [\Modules\Backup\Controllers\Admin\BackupController::class, 'delete'])->name('admin.backups.delete');

    // Monitoring
    Route::get('/health', [\Modules\Backup\Controllers\Admin\MonitoringController::class, 'index'])->name('admin.backups.health');
    Route::get('/stats', [\Modules\Backup\Controllers\Admin\MonitoringController::class, 'stats'])->name('admin.backups.stats');
});
