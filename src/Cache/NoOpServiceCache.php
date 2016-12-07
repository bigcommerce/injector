<?php
namespace Bigcommerce\Injector\Cache;

/**
 * No-Op service cache for testing/benchmarking purposes. Behaves as if there is no cache.
 */
class NoOpServiceCache implements ServiceCacheInterface
{
    /**
     * Retrieve the value of a key in the cache.
     *
     * @param string $key
     * @return string|false cached string value or false when key not present in a cache
     */
    public function get($key)
    {
        return false;
    }

    /**
     * Save a key/value pair to the cache.
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function set($key, $value)
    {
        return;
    }

    /**
     * Remove a key from the cache.
     *
     * @param string $key
     * @return void
     */
    public function remove($key)
    {
        return;
    }
}
