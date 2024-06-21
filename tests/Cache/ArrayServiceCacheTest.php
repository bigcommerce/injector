<?php
namespace Tests\Cache;

use Bigcommerce\Injector\Cache\ArrayServiceCache;
use PHPUnit\Framework\TestCase;

/**
 *
 * @coversDefaultClass \Bigcommerce\Injector\Cache\ArrayServiceCache
 */
class ArrayServiceCacheTest extends TestCase
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

    public function testHasReturnsTrueForExistingKey(): void
    {
        $cache = new ArrayServiceCache();
        $cache->set("test", "Abc");

        $hasEntry = $cache->has("test");

        $this->assertTrue($hasEntry);
    }

    public function testHasReturnsFalseForMissingKey(): void
    {
        $cache = new ArrayServiceCache();
        $cache->set("test2", "Abc");

        $hasEntry = $cache->has("test");

        $this->assertFalse($hasEntry);
    }

    public function testCountChecksTheNumberOfEntries(): void
    {
        $cache = new ArrayServiceCache();
        $cache->set("test1", "Abc");
        $cache->set("test2", "Def");

        $count = $cache->count();

        $this->assertEquals(2, $count);
    }

    public function testGetAllRetrievesAllCacheEntries(): void
    {
        $cache = new ArrayServiceCache();
        $cache->set("test1", 123);
        $cache->set("test2", 456);

        $allEntries = $cache->getAll();

        $this->assertEquals(['test1' => 123, 'test2' => 456], $allEntries);
    }
}
