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
     * @return mixed|false cached string value or false when key not present in a cache
     */
    public function get(string $key): mixed
    {
        return false;
    }

    /**
     * Save a key/value pair to the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        return;
    }

    /**
     * Remove a key from the cache.
     *
     * @param string $key
     * @return void
     */
    public function remove(string $key): void
    {
        return;
    }

    public function has(string $key): bool
    {
        return false;
    }
}
