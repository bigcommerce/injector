<?php
namespace tests\Adapter;

use ArrayAccess;
use Bigcommerce\Injector\Adapter\ArrayContainerAdapter;
use Bigcommerce\Injector\Adapter\Exception\ServiceNotFoundException;
use Bigcommerce\Injector\FindResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrayContainerAdapterTest
 * @package tests\Adapter
 */
#[CoversClass(ArrayContainerAdapter::class)]
class ArrayContainerAdapterTest extends TestCase
{
    public function testHappy()
    {
        $adapter = new ArrayContainerAdapter(["fish" => 123]);
        $this->assertTrue($adapter->has("fish"));
        $this->assertEquals(123, $adapter->get("fish"));
    }

    public function testGetMissing()
    {
        $this->expectException(ServiceNotFoundException::class);
        $adapter = new ArrayContainerAdapter([]);
        $adapter->get("Missing");
    }

    public function testGetFound()
    {
        $adapter = new ArrayContainerAdapter(["found" => 123]);
        $this->assertEquals(123, $adapter->get("found"));
    }

    public function testHasMissing()
    {
        $adapter = new ArrayContainerAdapter([]);
        $this->assertFalse($adapter->has("Missing"));
    }

    public function testHasFound()
    {
        $adapter = new ArrayContainerAdapter(["found" => 123]);
        $this->assertTrue($adapter->has("found"));
    }

    public function testFindReturnsEntry()
    {
        $adapter = new ArrayContainerAdapter(["found" => 123]);
        $this->assertSame(123, $adapter->find("found"));
    }

    public function testFindReturnsNotFoundSentinelWhenAbsent()
    {
        $adapter = new ArrayContainerAdapter([]);
        $this->assertSame(FindResult::NotFound, $adapter->find("Missing"));
    }

    public function testFindReturnsNullForPresentEntryThatResolvesToNull()
    {
        // Mimics Pimple/JITContainer: offsetExists is true for a registered service even when its
        // factory resolves to null. find() must return that null as a real value, not the NotFound
        // sentinel, so the Injector can pass it to a nullable dependency.
        $container = new class implements ArrayAccess {
            public function offsetExists($offset): bool
            {
                return $offset === 'present-but-null';
            }

            public function offsetGet($offset): mixed
            {
                return null;
            }

            public function offsetSet($offset, $value): void
            {
            }

            public function offsetUnset($offset): void
            {
            }
        };

        $adapter = new ArrayContainerAdapter($container);

        $this->assertNull($adapter->find("present-but-null"));
        $this->assertSame(FindResult::NotFound, $adapter->find("absent"));
    }
}
