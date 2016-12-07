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
    public function get($key);

    /**
     * Save a key/value pair to the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value);

    /**
     * Remove a key from the cache.
     *
     * @param string $key
     * @return void
     */
    public function remove($key);
}
