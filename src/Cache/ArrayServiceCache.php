<?php
namespace Bigcommerce\Injector\Cache;

/**
 * In process, memory array service cache.
 */
class ArrayServiceCache implements ServiceCacheInterface
{
    /**
     * @var array
     */
    private $values = [];

    /**
     * ArrayCache constructor.
     * @param array $values
     */
    public function __construct($values = [])
    {
        $this->values = $values;
    }

    /**
     * Retrieve the value of a key in the cache.
     *
     * @param string $key
     * @return string|false cached string value or false when key not present in a cache
     */
    public function get($key)
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
     * @param string $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * Remove a key from the cache.
     *
     * @param string $key
     * @return void
     */
    public function remove($key)
    {
        if (!isset($this->values[$key])) {
            return;
        }
        unset($this->values[$key]);
    }
}
