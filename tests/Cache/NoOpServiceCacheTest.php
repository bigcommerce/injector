<?php
namespace Tests\Cache;

use Bigcommerce\Injector\Cache\NoOpServiceCache;

/**
 *
 * @coversDefaultClass Bigcommerce\Injector\Cache\NoOpServiceCache
 */
class NoOpServiceCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::get
     */
    public function testGet()
    {
        $cache = new NoOpServiceCache();
        $cache->set("test", 123);
        $this->assertFalse($cache->get("test"));
    }

    /**
     * @covers ::get
     */
    public function testGetMiss()
    {
        $cache = new NoOpServiceCache();
        $this->assertFalse($cache->get("test"));
    }

    /**
     * @covers ::remove
     */
    public function testRemoveMiss()
    {
        $cache = new NoOpServiceCache();
        $cache->remove("test");
        $this->assertFalse($cache->get("test"));
    }
    public function testRemove()
    {
        $cache = new NoOpServiceCache();
        $cache->set("test", "Abc");
        $this->assertFalse($cache->get("test"));
        $cache->remove("test");
        $this->assertFalse($cache->get("test"));
    }
}
