<?php

namespace Modules\Backup\Models;

use App\Core\Database\Model;

class Backup extends Model
{
    protected static string $table = 'backups';

    protected array $attributes = [
        'type' => '',
        'path' => '',
        'filename' => '',
        'disk' => 'local',
        'size' => 0,
        'status' => 'pending',
        'initiated_by' => 'system',
        'error_message' => null,
        'completed_at' => null,
    ];

    public function isSuccessful(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function getFullPath(): string
    {
        // This logic might depend on the storage driver, 
        // but for now we return the stored path
        return $this->path;
    }
}
