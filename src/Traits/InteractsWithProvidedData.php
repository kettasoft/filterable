<?php

namespace Kettasoft\Filterable\Traits;

/**
 * Trait to interact with provided data across Filterable instances.
 */
trait InteractsWithProvidedData
{
    /**
     * Provided data storage.
     * @var array
     */
    protected static $provided = [];

    /**
     * Get provided data.
     * 
     * @return array
     */
    public static function provides()
    {
        return self::$provided;
    }

    /**
     * Provide data to all Filterable instances.
     * 
     * @param array $data
     * @return void
     */
    public static function provide(array $data)
    {
        self::$provided = array_merge(self::$provided, $data);
    }

    /**
     * Check if provided data exists by key.
     * 
     * @param string $key
     * @return bool
     */
    public function hasProvided(string $key): bool
    {
        return array_key_exists($key, self::$provided);
    }

    /**
     * Get provided data by key.
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function provided(string|null $key = null, $default = null)
    {
        if ($key === null) {
            return self::$provided;
        }

        return $this->hasProvided($key) ? self::$provided[$key] : $default;
    }
}
