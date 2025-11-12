<?php

namespace Kettasoft\Filterable\Foundation\Caching;

use Illuminate\Database\Eloquent\Model;
use Kettasoft\Filterable\Foundation\Caching\FilterableCacheManager;

/**
 * CacheInvalidationObserver - Automatically invalidate caches when models change
 *
 * Observes model events (created, updated, deleted) and flushes
 * associated cache tags to ensure data consistency.
 *
 * @package Kettasoft\Filterable\Foundation\Caching
 */
class CacheInvalidationObserver
{
    /**
     * Cache manager instance
     *
     * @var FilterableCacheManager
     */
    protected FilterableCacheManager $cacheManager;

    /**
     * Model-to-tags mapping from configuration
     *
     * @var array
     */
    protected array $modelTagsMap;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cacheManager = app(FilterableCacheManager::class);
        $this->modelTagsMap = config('filterable.cache.auto_invalidate.models', []);
    }

    /**
     * Handle the Model "created" event
     *
     * @param Model $model
     * @return void
     */
    public function created(Model $model): void
    {
        $this->invalidateCacheForModel($model);
    }

    /**
     * Handle the Model "updated" event
     *
     * @param Model $model
     * @return void
     */
    public function updated(Model $model): void
    {
        $this->invalidateCacheForModel($model);
    }

    /**
     * Handle the Model "deleted" event
     *
     * @param Model $model
     * @return void
     */
    public function deleted(Model $model): void
    {
        $this->invalidateCacheForModel($model);
    }

    /**
     * Handle the Model "force deleted" event
     *
     * @param Model $model
     * @return void
     */
    public function forceDeleted(Model $model): void
    {
        $this->invalidateCacheForModel($model);
    }

    /**
     * Handle the Model "restored" event
     *
     * @param Model $model
     * @return void
     */
    public function restored(Model $model): void
    {
        $this->invalidateCacheForModel($model);
    }

    /**
     * Invalidate cache for a model
     *
     * @param Model $model
     * @return void
     */
    protected function invalidateCacheForModel(Model $model): void
    {
        $modelClass = get_class($model);

        // Find tags associated with this model
        $tags = $this->getTagsForModel($modelClass);

        if (!empty($tags)) {
            $this->cacheManager->flushByTags($tags);

            // Log invalidation if tracking is enabled
            if (config('filterable.cache.tracking.enabled', false)) {
                $this->logInvalidation($modelClass, $tags);
            }
        }
    }

    /**
     * Get cache tags for a model class
     *
     * @param string $modelClass
     * @return array
     */
    protected function getTagsForModel(string $modelClass): array
    {
        return $this->modelTagsMap[$modelClass] ?? [];
    }

    /**
     * Log cache invalidation
     *
     * @param string $modelClass
     * @param array $tags
     * @return void
     */
    protected function logInvalidation(string $modelClass, array $tags): void
    {
        $channel = config('filterable.cache.tracking.log_channel', 'daily');

        \Illuminate\Support\Facades\Log::channel($channel)->info(
            'Filterable cache invalidated',
            [
                'model' => $modelClass,
                'tags' => $tags,
                'timestamp' => now()->toIso8601String(),
            ]
        );
    }

    /**
     * Register the observer for auto-invalidation
     *
     * @return void
     */
    public static function register(): void
    {
        if (!config('filterable.cache.auto_invalidate.enabled', false)) {
            return;
        }

        $modelTagsMap = config('filterable.cache.auto_invalidate.models', []);

        foreach ($modelTagsMap as $modelClass => $tags) {
            if (class_exists($modelClass)) {
                $modelClass::observe(new static());
            }
        }
    }
}
