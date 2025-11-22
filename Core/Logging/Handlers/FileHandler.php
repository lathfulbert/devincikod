<?php

namespace App\Core\Logging\Handlers;

class FileHandler extends AbstractHandler
{
    protected string $filePath;
    protected $fileHandle;

    public function __construct(string $filePath, int $level = 0)
    {
        parent::__construct($level);
        $this->filePath = $filePath;

        // Create directory if it doesn't exist
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    protected function write(array $record): void
    {
        $formatted = $this->getFormatter()->format($record);

        if (!$this->fileHandle) {
            $this->fileHandle = fopen($this->filePath, 'a');
            if ($this->fileHandle === false) {
                throw new \RuntimeException("Unable to open log file: {$this->filePath}");
            }
        }

        fwrite($this->fileHandle, $formatted);
    }

    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }
}
