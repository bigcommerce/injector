<?php
namespace Tests\Reflection;

use Bigcommerce\Injector\Cache\ArrayServiceCache;
use Bigcommerce\Injector\Cache\ServiceCacheInterface;
use Bigcommerce\Injector\Reflection\ParameterInspector;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Tests\Dummy\MagicCallDummy;

/**
 *
 * @coversDefaultClass \Bigcommerce\Injector\Reflection\ParameterInspector
 */
class ParameterInspectorTest extends TestCase
{
    /** @var ObjectProphecy|ServiceCacheInterface */
    private $cache;

    public function setUp(): void
    {
        parent::setUp();
        $this->cache = $this->prophesize(ArrayServiceCache::class);
    }

    /**
     * @covers ::getSignatureByReflectionClass
     */
    public function test()
    {
        $ref = new ParameterInspector($this->cache->reveal());
        $signature = $ref->getSignatureByReflectionClass(new \ReflectionClass($this), "example1");
        $this->assertEquals(
            ["name" => "cache", "type" => ArrayServiceCache::class],
            $signature[0]
        );
    }

    public function testGetSignatureTypes()
    {
        $ref = new ParameterInspector($this->cache->reveal());
        $signature = $ref->getSignatureByClassName(self::class, "example1");
        $this->assertEquals(
            ["name" => "cache", "type" => ArrayServiceCache::class],
            $signature[0]
        );
    }

    public function testGetSignatureDefaults()
    {
        $ref = new ParameterInspector($this->cache->reveal());
        $signature = $ref->getSignatureByClassName(self::class, "example1");
        $this->assertCount(2, $signature);
        $this->assertEquals(
            ["name" => "default", "default" => 1],
            $signature[1]
        );
    }

    public function testGetSignatureNoDefaults()
    {
        $ref = new ParameterInspector($this->cache->reveal());
        $signature = $ref->getSignatureByClassName(self::class, "example2");
        $this->assertCount(2, $signature);
        $this->assertEquals(
            [
                ["name" => "noDefault"],
                ["name" => "noDefault2"]
            ],
            $signature
        );
    }

    public function testGetSignatureInvalidMethod()
    {
        $this->expectException(\ReflectionException::class);
        $ref = new ParameterInspector($this->cache->reveal());
        $ref->getSignatureByClassName(self::class, "invalidMethod");
    }

    public function testGetSignatureMagicCallMethod()
    {
        $ref = new ParameterInspector($this->cache->reveal());
        $signature = $ref->getSignatureByClassName(MagicCallDummy::class, "strangeMethodName");
        $this->assertEquals([], $signature);
    }

    public function testGetSignatureNoParameters()
    {
        $ref = new ParameterInspector($this->cache->reveal());
        $signature = $ref->getSignatureByClassName(self::class, "example3");
        $this->assertEquals([], $signature);
    }

    public function testGetSignatureVariadicParameter()
    {
        $ref = new ParameterInspector($this->cache->reveal());
        $signature = $ref->getSignatureByClassName(self::class, "example4");
        $this->assertEquals(
            ["name" => "args", "variadic" => true],
            $signature[0]
        );
    }

    public function testCacheHit()
    {
        $cacheSignature = [
            ["name" => "secretParameter", "type" => null, "default" => 1]
        ];
        $this->cache->get(self::class . "::secretMethod")->willReturn($cacheSignature);
        $ref = new ParameterInspector($this->cache->reveal());

        $signature = $ref->getSignatureByClassName(self::class, "secretMethod");
        $this->assertEquals($cacheSignature, $signature);
    }

    /**
     * THIS METHOD IS INSPECTED AS PART OF THIS TEST. DO NOT REMOVE
     * @param ArrayServiceCache $cache
     * @param int $default
     */
    private function example1(ArrayServiceCache $cache, $default = 1)
    {
    }

    /**
     * THIS METHOD IS INSPECTED AS PART OF THIS TEST. DO NOT REMOVE
     * @param $noDefault
     * @param $noDefault2
     * @return void
     */
    private function example2($noDefault, $noDefault2)
    {
    }

    /**
     * THIS METHOD IS INSPECTED AS PART OF THIS TEST. DO NOT REMOVE
     * No parameters
     * @return void
     */
    private function example3()
    {
    }

    /**
     * THIS METHOD IS INSPECTED AS PART OF THIS TEST. DO NOT REMOVE
     * No parameters
     * @return void
     */
    private function example4(string ...$args)
    {
    }
}
