<?php

declare(strict_types=1);

namespace Modules\Backup\Models;

use App\Core\Database\Model;

/**
 * Backup Model
 * 
 * @property int $id
 * @property string $type
 * @property string $path
 * @property string $filename
 * @property string $disk
 * @property int $size
 * @property string $status
 * @property string $initiated_by
 * @property string|null $error_message
 * @property string|null $completed_at
 * @property string $created_at
 * @property string|null $updated_at
 * 
 * @method void save()
 * @method bool update(array $data)
 * @method void delete()
 */
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
