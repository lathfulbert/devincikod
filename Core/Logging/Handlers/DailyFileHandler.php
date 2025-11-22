<?php

namespace App\Core\Logging\Handlers;

use DateTime;

class DailyFileHandler extends AbstractHandler
{
    protected string $baseFilePath;
    protected int $maxFiles;
    protected ?string $currentDate = null;
    protected ?FileHandler $currentHandler = null;

    public function __construct(string $baseFilePath, int $maxFiles = 7, int $level = 0)
    {
        parent::__construct($level);
        $this->baseFilePath = $baseFilePath;
        $this->maxFiles = $maxFiles;
    }

    protected function write(array $record): void
    {
        $date = $record['datetime']->format('Y-m-d');

        // Create new handler if date changed
        if ($date !== $this->currentDate) {
            $this->currentDate = $date;
            $filePath = $this->getFilePath($date);
            $this->currentHandler = new FileHandler($filePath, $this->level);
            $this->currentHandler->setFormatter($this->getFormatter());

            // Cleanup old files
            $this->cleanupOldFiles();
        }

        $this->currentHandler->handle($record);
    }

    protected function getFilePath(string $date): string
    {
        $info = pathinfo($this->baseFilePath);
        $dirname = $info['dirname'];
        $filename = $info['filename'];
        $extension = $info['extension'] ?? 'log';

        return "{$dirname}/{$filename}-{$date}.{$extension}";
    }

    protected function cleanupOldFiles(): void
    {
        $info = pathinfo($this->baseFilePath);
        $dirname = $info['dirname'];
        $filename = $info['filename'];
        $extension = $info['extension'] ?? 'log';

        if (!is_dir($dirname)) {
            return;
        }

        $pattern = "{$dirname}/{$filename}-*.{$extension}";
        $files = glob($pattern);

        if (count($files) <= $this->maxFiles) {
            return;
        }

        // Sort by modification time
        usort($files, function ($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        // Remove oldest files
        $filesToRemove = array_slice($files, 0, count($files) - $this->maxFiles);
        foreach ($filesToRemove as $file) {
            @unlink($file);
        }
    }
}
