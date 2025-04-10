<?php
namespace tests\Adapter;

use Bigcommerce\Injector\Adapter\ArrayContainerAdapter;
use Bigcommerce\Injector\Adapter\Exception\ServiceNotFoundException;
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
}
