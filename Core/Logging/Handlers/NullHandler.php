<?php

namespace App\Core\Logging\Handlers;

class NullHandler extends AbstractHandler
{
    protected function write(array $record): void
    {
        // Do nothing - this handler discards all logs
    }
}
