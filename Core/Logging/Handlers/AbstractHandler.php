<?php

namespace App\Core\Logging\Handlers;

use App\Core\Logging\HandlerInterface;

abstract class AbstractHandler implements HandlerInterface
{
    protected int $level;
    protected $formatter;

    public function __construct(int $level = 0)
    {
        $this->level = $level;
    }

    public function isHandling(array $record): bool
    {
        return $record['level_value'] >= $this->level;
    }

    public function handle(array $record): void
    {
        if (!$this->isHandling($record)) {
            return;
        }

        $this->write($record);
    }

    abstract protected function write(array $record): void;

    public function setFormatter($formatter): self
    {
        $this->formatter = $formatter;
        return $this;
    }

    protected function getFormatter()
    {
        if (!$this->formatter) {
            $this->formatter = new \App\Core\Logging\Formatters\LineFormatter();
        }
        return $this->formatter;
    }
}
