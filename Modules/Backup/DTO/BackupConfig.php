<?php

declare(strict_types=1);

namespace Modules\Backup\DTO;

use Modules\Backup\Enums\BackupType;

/**
 * Backup Configuration Data Transfer Object
 * 
 * Demonstrates Constructor Property Promotion (PHP 8.0+)
 * and Readonly Properties (PHP 8.1+)
 */
class BackupConfig
{
    public function __construct(
        public readonly BackupType $type,
        public readonly string $disk = 'local',
        public readonly bool $compress = true,
        public readonly array $exclude = [],
        public readonly ?string $password = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] instanceof BackupType ? $data['type'] : BackupType::from($data['type'] ?? 'full'),
            disk: $data['disk'] ?? 'local',
            compress: $data['compress'] ?? true,
            exclude: $data['exclude'] ?? [],
            password: $data['password'] ?? null,
        );
    }
}
