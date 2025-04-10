<?php
namespace Tests\Cache;

use Bigcommerce\Injector\Cache\NoOpServiceCache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoOpServiceCache::class)]
class NoOpServiceCacheTest extends TestCase
{
    public function testGet()
    {
        $cache = new NoOpServiceCache();
        $cache->set("test", 123);
        $this->assertFalse($cache->get("test"));
    }

    public function testGetMiss()
    {
        $cache = new NoOpServiceCache();
        $this->assertFalse($cache->get("test"));
    }

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
