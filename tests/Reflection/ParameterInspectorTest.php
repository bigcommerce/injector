<?php
namespace Tests\Reflection;

use Bigcommerce\Injector\Reflection\ParameterInspector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Tests\Dummy\DummyNoConstructor;
use Tests\Dummy\MagicCallDummy;

#[CoversClass(ParameterInspector::class)]
class ParameterInspectorTest extends TestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test()
    {
        $ref = new ParameterInspector();
        $signature = $ref->getSignatureByReflectionClass(new \ReflectionClass($this), "example1");
        $this->assertEquals(
            ["name" => "dummyNoConstructor", "type" => DummyNoConstructor::class],
            $signature[0]
        );
    }

    public function testGetSignatureTypes()
    {
        $ref = new ParameterInspector();
        $signature = $ref->getSignatureByClassName(self::class, "example1");
        $this->assertEquals(
            ["name" => "dummyNoConstructor", "type" => DummyNoConstructor::class],
            $signature[0]
        );
    }

    public function testGetSignatureDefaults()
    {
        $ref = new ParameterInspector();
        $signature = $ref->getSignatureByClassName(self::class, "example1");
        $this->assertCount(2, $signature);
        $this->assertEquals(
            ["name" => "default", "default" => 1],
            $signature[1]
        );
    }

    public function testGetSignatureNoDefaults()
    {
        $ref = new ParameterInspector();
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
        $ref = new ParameterInspector();
        $ref->getSignatureByClassName(self::class, "invalidMethod");
    }

    public function testGetSignatureMagicCallMethod()
    {
        $ref = new ParameterInspector();
        $signature = $ref->getSignatureByClassName(MagicCallDummy::class, "strangeMethodName");
        $this->assertEquals([], $signature);
    }

    public function testGetSignatureNoParameters()
    {
        $ref = new ParameterInspector();
        $signature = $ref->getSignatureByClassName(self::class, "example3");
        $this->assertEquals([], $signature);
    }

    public function testGetSignatureVariadicParameter()
    {
        $ref = new ParameterInspector();
        $signature = $ref->getSignatureByClassName(self::class, "example4");
        $this->assertEquals(
            ["name" => "args", "variadic" => true],
            $signature[0]
        );
    }

    /**
     * THIS METHOD IS INSPECTED AS PART OF THIS TEST. DO NOT REMOVE
     * @param DummyNoConstructor $dummyNoConstructor
     * @param int $default
     */
    private function example1(DummyNoConstructor $dummyNoConstructor, $default = 1)
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
