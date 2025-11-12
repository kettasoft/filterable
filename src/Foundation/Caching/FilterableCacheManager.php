<?php

namespace Kettasoft\Filterable\Foundation\Caching;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Cache\TaggableStore;
use Illuminate\Cache\TaggedCache;
use DateTimeInterface;

/**
 * FilterableCacheManager - Singleton cache management for Filterable
 *
 * Provides centralized caching operations for all filterable instances with
 * support for TTL, tags, scopes, profiles, and auto-invalidation.
 *
 * @package Kettasoft\Filterable\Foundation\Caching
 */
class FilterableCacheManager
{
    /**
     * Singleton instance
     *
     * @var FilterableCacheManager|null
     */
    private static ?FilterableCacheManager $instance = null;

    /**
     * Cache repository instance
     *
     * @var CacheRepository
     */
    protected CacheRepository $cache;

    /**
     * Cache configuration
     *
     * @var array
     */
    protected array $config;

    /**
     * Active cache tags
     *
     * @var array
     */
    protected array $tags = [];

    /**
     * Active cache scopes
     *
     * @var array
     */
    protected array $scopes = [];

    /**
     * Current cache profile
     *
     * @var string|null
     */
    protected ?string $profile = null;

    /**
     * Whether caching is globally enabled
     *
     * @var bool
     */
    protected bool $enabled = true;

    /**
     * Private constructor for singleton
     *
     * @param CacheRepository $cache
     * @param array $config
     */
    private function __construct(CacheRepository $cache, array $config = [])
    {
        $this->cache = $cache;
        $this->config = $config;
        $this->enabled = $config['enabled'] ?? true;
    }

    /**
     * Get singleton instance
     *
     * @return FilterableCacheManager
     */
    public static function getInstance(): FilterableCacheManager
    {
        if (self::$instance === null) {
            $config = config('filterable.cache', []);
            $driver = $config['driver'] ?? config('cache.default');

            self::$instance = new self(
                Cache::store($driver),
                $config
            );
        }

        return self::$instance;
    }

    /**
     * Reset singleton instance (for testing)
     *
     * @return void
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Cache a value with the given key
     *
     * @param string $key
     * @param mixed $value
     * @param DateTimeInterface|int|null $ttl
     * @return bool
     */
    public function put(string $key, mixed $value, DateTimeInterface|int|null $ttl = null): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $ttl = $ttl ?? $this->getDefaultTtl();
        $cache = $this->getCacheInstance();

        return $cache->put($key, $value, $ttl);
    }

    /**
     * Get a value from cache or execute callback and cache result
     *
     * @param string $key
     * @param DateTimeInterface|int|null $ttl
     * @param callable $callback
     * @return mixed
     */
    public function remember(string $key, DateTimeInterface|int|null $ttl, callable $callback): mixed
    {
        if (!$this->enabled) {
            return $callback();
        }

        $ttl = $ttl ?? $this->getDefaultTtl();
        $cache = $this->getCacheInstance();

        return $cache->remember($key, $ttl, $callback);
    }

    /**
     * Cache a value forever
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function forever(string $key, mixed $value): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $cache = $this->getCacheInstance();

        return $cache->forever($key, $value);
    }

    /**
     * Get a value from cache or execute callback and cache forever
     *
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public function rememberForever(string $key, callable $callback): mixed
    {
        if (!$this->enabled) {
            return $callback();
        }

        $cache = $this->getCacheInstance();

        return $cache->rememberForever($key, $callback);
    }

    /**
     * Retrieve a value from cache
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, $default = null): mixed
    {
        if (!$this->enabled) {
            return $default;
        }

        $cache = $this->getCacheInstance();

        return $cache->get($key, $default);
    }

    /**
     * Check if a key exists in cache
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $cache = $this->getCacheInstance();

        return $cache->has($key);
    }

    /**
     * Remove a value from cache
     *
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        $cache = $this->getCacheInstance();

        return $cache->forget($key);
    }

    /**
     * Flush all cache entries with the given tags
     *
     * @param array $tags
     * @return bool
     */
    public function flushByTags(array $tags): bool
    {
        if (empty($tags)) {
            return false;
        }

        if ($this->cache->getStore() instanceof TaggableStore) {
            /** @var \Illuminate\Cache\CacheManager $cacheManager */
            $cacheManager = app('cache');
            return $cacheManager->tags($tags)->flush();
        }

        // If tags not supported, do nothing
        return false;
    }

    /**
     * Set cache tags for the next operation
     *
     * @param array $tags
     * @return self
     */
    public function withTags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * Set cache scopes for the next operation
     *
     * @param array $scopes
     * @return self
     */
    public function withScopes(array $scopes): self
    {
        $this->scopes = $scopes;
        return $this;
    }

    /**
     * Add a single scope
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function addScope(string $key, mixed $value): self
    {
        $this->scopes[$key] = $value;
        return $this;
    }

    /**
     * Set cache profile for the next operation
     *
     * @param string $profile
     * @return self
     */
    public function withProfile(string $profile): self
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * Generate a cache key with scopes
     *
     * @param string $baseKey
     * @return string
     */
    public function generateKey(string $baseKey): string
    {
        if (empty($this->scopes)) {
            return $baseKey;
        }

        $scopeString = collect($this->scopes)
            ->sortKeys()
            ->map(fn($value, $key) => "{$key}:{$value}")
            ->implode(':');

        return "{$baseKey}:{$scopeString}";
    }

    /**
     * Get cache instance with tags if applicable
     *
     * @return CacheRepository|TaggedCache
     */
    protected function getCacheInstance(): CacheRepository|TaggedCache
    {
        if (!empty($this->tags) && $this->cache->getStore() instanceof TaggableStore) {
            /** @var \Illuminate\Cache\CacheManager $cacheManager */
            $cacheManager = app('cache');
            return $cacheManager->tags($this->tags);
        }

        return $this->cache;
    }

    /**
     * Get default TTL from config
     *
     * @return int
     */
    protected function getDefaultTtl(): int
    {
        if ($this->profile && isset($this->config['profiles'][$this->profile]['ttl'])) {
            return $this->config['profiles'][$this->profile]['ttl'];
        }

        return $this->config['default_ttl'] ?? 3600;
    }

    /**
     * Get profile configuration
     *
     * @param string $profile
     * @return array
     */
    public function getProfileConfig(string $profile): array
    {
        return $this->config['profiles'][$profile] ?? [];
    }

    /**
     * Check if a profile exists
     *
     * @param string $profile
     * @return bool
     */
    public function hasProfile(string $profile): bool
    {
        return isset($this->config['profiles'][$profile]);
    }

    /**
     * Enable caching globally
     *
     * @return self
     */
    public function enable(): self
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * Disable caching globally
     *
     * @return self
     */
    public function disable(): self
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * Check if caching is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Reset tags, scopes, and profile for next operation
     *
     * @return self
     */
    public function reset(): self
    {
        $this->tags = [];
        $this->scopes = [];
        $this->profile = null;
        return $this;
    }

    /**
     * Get current tags
     *
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Get current scopes
     *
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * Get current profile
     *
     * @return string|null
     */
    public function getProfile(): ?string
    {
        return $this->profile;
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
        //
    }

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
