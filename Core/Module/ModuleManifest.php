<?php

namespace App\Core\Module;

/**
 * Class ModuleManifest
 * 
 * Represents a parsed module.json configuration file.
 * Immutable value object following SOLID principles.
 */
class ModuleManifest
{
    public function __construct(
        public readonly string $name,
        public readonly string $version,
        public readonly string $description,
        public readonly string $author,
        public readonly string $license,
        public readonly array $dependencies,
        public readonly array $autoload,
        public readonly array $permissions,
        public readonly array $settings,
        public readonly array $assets
    ) {}

    /**
     * Create manifest from JSON file.
     */
    public static function fromFile(string $path): ?self
    {
        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON in module manifest: {$path}");
        }

        return self::fromArray($data);
    }

    /**
     * Create manifest from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            version: $data['version'] ?? '1.0.0',
            description: $data['description'] ?? '',
            author: $data['author'] ?? '',
            license: $data['license'] ?? 'MIT',
            dependencies: $data['dependencies'] ?? [],
            autoload: $data['autoload'] ?? [
                'routes' => true,
                'migrations' => true,
                'services' => true,
                'permissions' => true,
                'views' => true,
                'assets' => true,
                'config' => true
            ],
            permissions: $data['permissions'] ?? [],
            settings: $data['settings'] ?? [],
            assets: $data['assets'] ?? ['js' => [], 'css' => []]
        );
    }

    /**
     * Validate manifest data.
     */
    public function validate(): bool
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException("Module name is required");
        }

        if (empty($this->version)) {
            throw new \InvalidArgumentException("Module version is required");
        }

        // Validate version format (semantic versioning)
        if (!preg_match('/^\d+\.\d+\.\d+/', $this->version)) {
            throw new \InvalidArgumentException("Invalid version format. Use semantic versioning (e.g., 1.0.0)");
        }

        return true;
    }

    /**
     * Convert manifest to array.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'description' => $this->description,
            'author' => $this->author,
            'license' => $this->license,
            'dependencies' => $this->dependencies,
            'autoload' => $this->autoload,
            'permissions' => $this->permissions,
            'settings' => $this->settings,
            'assets' => $this->assets
        ];
    }

    /**
     * Convert manifest to JSON.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Check if autoload is enabled for a resource type.
     */
    public function shouldAutoload(string $type): bool
    {
        return $this->autoload[$type] ?? false;
    }
}
