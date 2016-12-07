<?php
namespace Tests\Cache;

use Bigcommerce\Injector\Cache\ArrayServiceCache;

/**
 *
 * @coversDefaultClass \Bigcommerce\Injector\Cache\ArrayServiceCache
 */
class ArrayServiceCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::get
     */
    public function testGet()
    {
        $cache = new ArrayServiceCache();
        $cache->set("test", 123);
        $this->assertEquals(123, $cache->get("test"));
    }

    /**
     * @covers ::get
     */
    public function testGetMiss()
    {
        $cache = new ArrayServiceCache();
        $this->assertFalse($cache->get("test"));
    }

    /**
     * @covers ::remove
     */
    public function testRemoveMiss()
    {
        $cache = new ArrayServiceCache();
        $cache->remove("test");
        $this->assertFalse($cache->get("test"));
    }
    public function testRemove()
    {
        $cache = new ArrayServiceCache();
        $cache->set("test", "Abc");
        $this->assertEquals("Abc", $cache->get("test"));
        $cache->remove("test");
        $this->assertFalse($cache->get("test"));
    }
}
