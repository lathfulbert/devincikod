<?php

declare(strict_types=1);

namespace Modules\Backup\Services;

use Modules\Backup\Models\Backup;
use Modules\Backup\Services\Storage\LocalDriver;
use Modules\Backup\Services\Storage\StorageDriverInterface;
use Modules\Backup\Enums\BackupType;
use Modules\Backup\Enums\BackupStatus;

/**
 * Backup Service
 * 
 * Demonstrates constructor property promotion (PHP 8.0+)
 */
class ExampleService
{
    /**
     * Constructor with promoted properties
     * 
     * @param string $name Service name
     * @param bool $enabled Whether service is enabled
     * @param array<string> $options Service options
     */
    public function __construct(
        private readonly string $name,
        private readonly bool $enabled = true,
        private readonly array $options = [],
    ) {
        // Properties are automatically assigned!
        // No need for $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
