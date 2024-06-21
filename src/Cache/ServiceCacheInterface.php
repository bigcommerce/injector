<?php
namespace Bigcommerce\Injector\Cache;

/**
 * The ServiceCache is a specialised implementation of the core application caches optimised for Injector/Service
 * caching purposes in the BC app. Specifically:
 * - Favour consuming memory rather than CPU cycles
 * - Speed of fetches
 * - Avoid collisions with other clients
 */
interface ServiceCacheInterface
{

    /**
     * Retrieve the value of a key in the cache.
     *
     * @param string $key
     * @return mixed|false cached value or false when key not present in a cache
     */
    public function get(string $key): mixed;

    /**
     * Save a key/value pair to the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value);

    /**
     * Check if a key exists in the cache.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Remove a key from the cache.
     *
     * @param string $key
     * @return void
     */
    public function remove(string $key): void;
}
