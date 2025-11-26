<?php

namespace Modules\Backup\Events;

use Modules\Backup\Models\Backup;

class BackupSuccessful
{
    public Backup $backup;

    public function __construct(Backup $backup)
    {
        $this->backup = $backup;
    }
}
