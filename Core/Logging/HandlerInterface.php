<?php

namespace App\Core\Logging;

interface HandlerInterface
{
    /**
     * Handle a log record.
     */
    public function handle(array $record): void;

    /**
     * Check if this handler accepts the given level.
     */
    public function isHandling(array $record): bool;
}
