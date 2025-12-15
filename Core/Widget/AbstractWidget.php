<?php

namespace App\Core\Widget;

use App\Core\Widget\Contracts\WidgetInterface;
use App\Core\Container\Container;
use Modules\RBAC\Services\RbacService;

/**
 * Classe abstraite AbstractWidget
 *
 * Classe de base pour tous les widgets du framework.
 * Fournit les fonctionnalités communes et force l'implémentation
 * des méthodes essentielles via WidgetInterface.
 */
abstract class AbstractWidget implements WidgetInterface
{
    /**
     * Vérifie si le widget est activé dans la config widgets_status.php
     */
    protected function isGloballyEnabled(): bool
    {
        $configPath = __DIR__ . '/../../../config/widgets_status.php';
        if (file_exists($configPath)) {
            $statusConfig = include $configPath;
            $name = $this->getName();
            if (array_key_exists($name, $statusConfig)) {
                return (bool)$statusConfig[$name];
            }
        }
        return $this->isEnabled();
    }

    /**
     * Par défaut, un widget ne doit pas être visible s'il est désactivé globalement
     */
    public function canView($user): bool
    {
        return $this->isGloballyEnabled();
    }
    protected string $name;
    protected string $type;
    protected ?string $permission = null;
    protected bool $cacheable = true;
    protected int $cacheDuration = 300; // 5 minutes par défaut
    protected array $config = [];
    protected bool $enabled = true; // Permet d'activer/désactiver le widget

    /**
     * Permet de savoir si le widget est activé
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Méthode abstraite que chaque widget doit implémenter
     * pour calculer et retourner ses données
     */
    abstract protected function calculateData(): array;

    /**
     * {@inheritDoc}
     */
    public function getData(): array
    {
        // Si le widget est cacheable, tenter de récupérer depuis le cache
        if ($this->isCacheable()) {
            $cacheKey = $this->getCacheKey();
            $cached = $this->getFromCache($cacheKey);

            if ($cached !== null) {
                return $cached;
            }
        }

        // Calculer les données
        $data = $this->calculateData();

        // Valider la structure des données
        $data = $this->validateDataStructure($data);

        // Mettre en cache si nécessaire
        if ($this->isCacheable()) {
            $this->putInCache($this->getCacheKey(), $data, $this->getCacheDuration());
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function getPermission(): ?string
    {
        return $this->permission;
    }

    /**
     * {@inheritDoc}
     */
    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheDuration(): int
    {
        return $this->cacheDuration;
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheKey(): string
    {
        return 'widget:' . $this->getName();
    }

    /**
     * {@inheritDoc}
     */

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        return array_merge([
            'name' => $this->getName(),
            'type' => $this->getType(),
            'permission' => $this->getPermission(),
            'cacheable' => $this->isCacheable(),
            'cacheDuration' => $this->getCacheDuration(),
        ], $this->config);
    }

    /**
     * Valide et normalise la structure des données du widget
     *
     * @param array $data
     * @return array
     */
    protected function validateDataStructure(array $data): array
    {
        $defaults = [
            'title' => '',
            'value' => '',
            'description' => '',
            'icon' => '',
            'trend' => [
                'percentage' => 0,
                'direction' => 'stable'
            ],
            'meta' => []
        ];

        return array_merge($defaults, $data);
    }

    /**
     * Récupère des données depuis le cache
     *
     * @param string $key
     * @return array|null
     */
    protected function getFromCache(string $key): ?array
    {
        try {
            $cacheFile = $this->getCacheFilePath($key);

            if (!file_exists($cacheFile)) {
                return null;
            }

            $content = file_get_contents($cacheFile);
            if ($content === false) {
                return null;
            }

            $data = json_decode($content, true);
            if (!isset($data['expires_at']) || !isset($data['data'])) {
                return null;
            }

            // Vérifier l'expiration
            if (time() > $data['expires_at']) {
                @unlink($cacheFile);
                return null;
            }

            return $data['data'];
        } catch (\Exception $e) {
            error_log("Widget cache read error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Stocke des données dans le cache
     *
     * @param string $key
     * @param array $data
     * @param int $duration
     * @return bool
     */
    protected function putInCache(string $key, array $data, int $duration): bool
    {
        try {
            $cacheFile = $this->getCacheFilePath($key);
            $cacheDir = dirname($cacheFile);

            // Créer le répertoire de cache si nécessaire
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }

            $content = json_encode([
                'expires_at' => time() + $duration,
                'data' => $data
            ]);

            return file_put_contents($cacheFile, $content) !== false;
        } catch (\Exception $e) {
            error_log("Widget cache write error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retourne le chemin du fichier de cache
     *
     * @param string $key
     * @return string
     */
    protected function getCacheFilePath(string $key): string
    {
        $safeKey = preg_replace('/[^a-z0-9_\-]/', '_', strtolower($key));
        return __DIR__ . '/../../storage/cache/widgets/' . $safeKey . '.json';
    }

    /**
     * Invalide le cache du widget
     *
     * @return bool
     */
    public function clearCache(): bool
    {
        try {
            $cacheFile = $this->getCacheFilePath($this->getCacheKey());
            if (file_exists($cacheFile)) {
                return @unlink($cacheFile);
            }
            return true;
        } catch (\Exception $e) {
            error_log("Widget cache clear error: " . $e->getMessage());
            return false;
        }
    }
}
