<?php

namespace Modules\Backup\Events;

use Modules\Backup\Models\Backup;

class BackupFailed
{
    public Backup $backup;
    public \Throwable $exception;

    public function __construct(Backup $backup, \Throwable $exception)
    {
        $this->backup = $backup;
        $this->exception = $exception;
    }
}
