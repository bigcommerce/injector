<?php
namespace Bigcommerce\Injector\Cache;

use Countable;

/**
 * In process, memory array service cache.
 * @template T
 * @implements MultiGetCacheInterface<T>
 */
class ArrayServiceCache implements ServiceCacheInterface, MultiGetCacheInterface, Countable
{
    /**
     * @var array<string, T>
     */
    private array $values;

    /**
     * @param array<string, T> $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /**
     * Retrieve the value of a key in the cache.
     *
     * @param string $key
     * @return T cached value or false when key not present in a cache
     */
    public function get(string $key): mixed
    {
        if (!isset($this->values[$key])) {
            return false;
        }
        return $this->values[$key];
    }

    /**
     * Save a key/value pair to the cache.
     *
     * @param string $key
     * @param T $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    /**
     * Remove a key from the cache.
     *
     * @param string $key
     * @return void
     */
    public function remove(string $key): void
    {
        if (!isset($this->values[$key])) {
            return;
        }
        unset($this->values[$key]);
    }

    /**
     * Check if a key exists in the cache.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->values[$key]);
    }

    /**
     * Retrieve all entries of the cache.
     *
     * @return array<string, T>
     */
    public function getAll(): array
    {
        return $this->values;
    }

    /**
     * Count elements of the cache.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->values);
    }
}
