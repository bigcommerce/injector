<?php
namespace Tests;

use Bigcommerce\Injector\Cache\ServiceCacheInterface;
use Bigcommerce\Injector\Exception\InjectorInvocationException;
use Bigcommerce\Injector\Injector;
use Bigcommerce\Injector\Reflection\ParameterInspector;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Tests\Dummy\DummyDependency;
use Tests\Dummy\DummyNoConstructor;
use Tests\Dummy\DummyPrivateConstructor;
use Tests\Dummy\DummySimpleConstructor;
use Tests\Dummy\DummyString;
use Tests\Dummy\DummySubDependency;
use Tests\Dummy\DummyVariadicConstructor;
use TypeError;

/**
 *
 * @coversDefaultClass \Bigcommerce\Injector\Injector
 */
class InjectorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ContainerInterface|ObjectProphecy
     */
    private $container;

    /**
     * @var ParameterInspector|ObjectProphecy
     */
    private $inspector;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->inspector = $this->prophesize(ParameterInspector::class);
    }

    /**
     * @covers ::create
     */
    public function testCreateNoConstructor()
    {
        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $instance = $injector->create(DummyNoConstructor::class);
        $this->assertInstanceOf(DummyNoConstructor::class, $instance);
    }

    /**
     * @covers ::create
     */
    public function testCreatePrivateConstructor()
    {
        $this->expectException(InjectorInvocationException::class);
        $this->expectExceptionMessageMatches(
            "/constructor isn't public/ims"
        );
        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $instance = $injector->create(DummyPrivateConstructor::class);
        $this->assertInstanceOf(DummyPrivateConstructor::class, $instance);
    }

    public function testAutoCreateWhiteList()
    {
        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $injector->addAutoCreate("Shipping\\\\.*");
        $this->assertCount(1, $injector->getAutoCreateWhiteList());
        $this->assertTrue($injector->canAutoCreate("Shipping\\AusPost\\StampCalculator"));
        $this->assertFalse($injector->canAutoCreate("Order\\Address\\Digital"));
    }

    /**
     * Injector should be able to construct objects from the given parameter array indexed by:
     *  - parameter name
     *  - parameter index
     *  - parameter type
     *  - parameter default value
     */
    public function testCreateFromParameters()
    {
        $cacheMock = $this->prophesize(ServiceCacheInterface::class)->reveal();
        $dummyDependency = new DummyDependency(new DummySubDependency());

        $this->mockDummySimpleSignature();

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $instance = $injector->create(
            DummySimpleConstructor::class,
            [
                "cache" => $cacheMock, //Parameter Name
                DummyDependency::class => $dummyDependency, //Parameter Type
                2 => "bob" //Parameter Index
                //Missing value - 'age' should use its default
            ]
        );
        $this->assertSame($cacheMock, $instance->getCache());
        $this->assertSame($dummyDependency, $instance->getDummyDependency());
        $this->assertEquals("bob", $instance->getName());
        $this->assertEquals(25, $instance->getAge());
        $this->assertEmpty($instance->getArgs());
    }

    public function testCreateVariadicParameterShouldNotSourceFromContainer()
    {
        $this->mockDummyVariadicSignature();

        $hello = new DummyString('hello');
        $this->container->has(DummyString::class)->willReturn(true);
        $this->container->get(DummyString::class)->willReturn($hello);

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $instance = $injector->create(DummyVariadicConstructor::class);

        $this->assertEmpty($instance->getArgs());
    }

    public function testCreateVariadicParameterOnly()
    {
        $this->mockDummyVariadicSignature();

        $hello = new DummyString('hello');
        $world = new DummyString('world');

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $instance = $injector->create(
            DummyVariadicConstructor::class,
            [
                $hello,
                $world,
            ]
        );

        $this->assertEquals([$hello, $world], $instance->getArgs());
    }

    public function testCreateBothNormalAndVariadicParameters()
    {
        $cacheMock = $this->prophesize(ServiceCacheInterface::class)->reveal();
        $dummyDependency = new DummyDependency(new DummySubDependency());
        $this->mockDummySimpleSignature();

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $instance = $injector->create(
            DummySimpleConstructor::class,
            [
                "cache" => $cacheMock, //Parameter Name
                DummyDependency::class => $dummyDependency, //Parameter Type
                2 => "bob", //Parameter Index
                3 => 10, //Prameter Index
                'hello', //Variadic
                'world',
            ]
        );

        $this->assertSame($cacheMock, $instance->getCache());
        $this->assertSame($dummyDependency, $instance->getDummyDependency());
        $this->assertEquals("bob", $instance->getName());
        $this->assertEquals(10, $instance->getAge());
        $this->assertEquals(['hello', 'world'], $instance->getArgs());
    }

    public function testCreateVariadicParameterConsumesAllUnusedProvidedParameters()
    {
        $this->mockDummyVariadicSignature();
        $hello = new DummyString('hello');
        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());

        $this->expectException(TypeError::class);
        $instance = $injector->create(
            DummyVariadicConstructor::class,
            [
                $hello,
                // parameter that is not declared on method signature and when piped
                // to variadic parameter should cause type error
                'ghostParameter' => 11,
            ]
        );
    }

    /**
     * Injector should be able to construct objects from the container indexed by:
     *  - parameter type
     *  - parameter default value
     */
    public function testCreateFromContainer()
    {
        $cacheMock = $this->prophesize(ServiceCacheInterface::class)->reveal();
        $dummyDependency = new DummyDependency(new DummySubDependency());

        $this->mockDummySimpleSignature();

        $this->container->has(ServiceCacheInterface::class)->willReturn(true);
        $this->container->get(ServiceCacheInterface::class)->willReturn($cacheMock);
        $this->container->has(DummyDependency::class)->willReturn(true);
        $this->container->get(DummyDependency::class)->willReturn($dummyDependency);

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $instance = $injector->create(
            DummySimpleConstructor::class,
            [
                //Missing value - 'cache' should come from container
                //Missing value - 'dummyDependency' should come from container
                2 => "bob" //Parameter Index
                //Missing value - 'age' should use its default
            ]
        );
        $this->assertSame($cacheMock, $instance->getCache());
        $this->assertSame($dummyDependency, $instance->getDummyDependency());
        $this->assertEquals("bob", $instance->getName());
        $this->assertEquals(25, $instance->getAge());
    }

    /**
     * Injector should be able to construct objects from the container indexed by:
     *  - parameter type
     *  - parameter default value
     */
    public function testAutoCreate()
    {
        $cacheMock = $this->prophesize(ServiceCacheInterface::class)->reveal();

        $this->mockDummySimpleSignature();
        $this->mockDummyDependencySignature();
        $this->mockDummySubDependencySignature();

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $injector->addAutoCreate(".*?Dummy.*?");
        $instance = $injector->create(
            DummySimpleConstructor::class,
            [
                ServiceCacheInterface::class => $cacheMock,
                //Missing value - 'dummyDependency' should come from auto-create
                2 => "bob" //Parameter Index
                //Missing value - 'age' should use its default
            ]
        );
        $this->assertSame($cacheMock, $instance->getCache());
        $this->assertInstanceOf(DummyDependency::class, $instance->getDummyDependency());
        $this->assertEquals("bob", $instance->getName());
        $this->assertEquals(25, $instance->getAge());
    }

    /**
     * Injector fails to create a sub-dependency. Should provided a wrapped stack exception message guiding
     * developers where to find the issue.
     */
    public function testAutoCreateStackWrap()
    {
        $this->expectException(InjectorInvocationException::class);
        $messageContains = [
            'Can\'t create ' . addslashes(DummyDependency::class) . '',
            'missing parameter \'\$dependency \[' . addslashes(DummySubDependency::class) . '\]\'',
            'Called when creating ' . addslashes(DummySimpleConstructor::class)
        ];
        $this->expectExceptionMessageMatches("/.*?" . implode(".*?", $messageContains) . ".*?/ims");
        $cacheMock = $this->prophesize(ServiceCacheInterface::class)->reveal();

        $this->mockDummySimpleSignature();
        $this->mockDummyDependencySignature();

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $injector->addAutoCreate(".*?DummyDependency");
        $instance = $injector->create(
            DummySimpleConstructor::class,
            [
                ServiceCacheInterface::class => $cacheMock,
                //Missing value - 'dummyDependency' should come from auto-create
                2 => "bob" //Parameter Index
                //Missing value - 'age' should use its default
            ]
        );
    }

    public function testCreateMissingParameter()
    {
        $this->expectException(InjectorInvocationException::class);
        $this->expectExceptionMessageMatches(
            '/missing parameter \'\$cache \[' . addslashes(ServiceCacheInterface::class) . '\]\'/ims'
        );
        $this->mockDummySimpleSignature();

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $instance = $injector->create(
            DummySimpleConstructor::class
        );
    }

    /**
     * @covers ::invoke
     */
    public function testInvokeOnNonObject()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Attempted Injector::invoke on a non-object: array.");
        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        //We're intentionally passing the wrong type to invoke here to assert the failure.
        /** @noinspection PhpParamsInspection */
        $injector->invoke([], "__construct");
    }

    /**
     * @covers ::invoke
     */
    public function testInvokeParameters()
    {
        $this->mockInspectorSignatureByClassName(
            DummyNoConstructor::class,
            "setAge",
            [
                ["name" => "age"]
            ]
        );

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        //We're intentionally passing the wrong type to invoke here to assert the failure.
        /** @noinspection PhpParamsInspection */
        $instance = new DummyNoConstructor();
        $injector->invoke($instance, "setAge", ["age" => 90]);
        $this->assertEquals(90, $instance->getAge());
    }

    /**
     * @covers ::invoke
     */
    public function testInvokeMissingRequiredParameter()
    {
        $this->expectException(InjectorInvocationException::class);
        $messageContains = [
            'Can\'t invoke method ' . addslashes(DummyNoConstructor::class) . '::setAge',
            'missing parameter \'\$age\''
        ];
        $this->expectExceptionMessageMatches("/" . implode(".*?", $messageContains) . "/ims");
        $this->mockInspectorSignatureByClassName(
            DummyNoConstructor::class,
            "setAge",
            [
                ["name" => "age"]
            ]
        );

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $instance = new DummyNoConstructor();
        $injector->invoke($instance, "setAge", []);
        $this->assertEquals(90, $instance->getAge());
    }

    /**
     * @covers ::invoke
     */
    public function testInvokeInvalidMethod()
    {
        $this->expectException(InjectorInvocationException::class);
        $messageContains = [
            'Failed to invoke ' . addslashes(DummyNoConstructor::class) . '::setName - method doesn\'t exist.'
        ];
        $this->expectExceptionMessageMatches("/" . implode(".*?", $messageContains) . "/ims");
        $this->inspector->getSignatureByClassName(DummyNoConstructor::class, "setName")->willThrow(
            new \ReflectionException("bad stuff")
        );

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $instance = new DummyNoConstructor();
        $injector->invoke($instance, "setName", []);
    }

    private function mockDummyDependencySignature()
    {
        $this->mockInspectorSignatureByReflectionClass(
            DummyDependency::class,
            "__construct",
            [
                ["name" => "dependency", "type" => DummySubDependency::class],
                ["name" => "enabled", "default" => true]
            ]
        );
    }

    private function mockDummySubDependencySignature()
    {
        $this->mockInspectorSignatureByReflectionClass(
            DummySubDependency::class,
            "__construct",
            [
                ["name" => "enabled", "default" => true]
            ]
        );
    }

    private function mockDummySimpleSignature()
    {
        $this->mockInspectorSignatureByReflectionClass(
            DummySimpleConstructor::class,
            "__construct",
            [
                ["name" => "cache", "type" => ServiceCacheInterface::class],
                ["name" => "dummyDependency", "type" => DummyDependency::class],
                ["name" => "name"],
                ["name" => "age", "default" => 25],
                ["name" => "args", "variadic" => true],
            ]
        );
    }

    private function mockDummyVariadicSignature()
    {
        $this->mockInspectorSignatureByReflectionClass(
            DummyVariadicConstructor::class,
            "__construct",
            [
                ["name" => "args", "variadic" => true],
            ]
        );
    }

    private function mockInspectorSignatureByReflectionClass($className, $methodName, $returns)
    {
        $this->inspector->getSignatureByReflectionClass(
            new \ReflectionClass($className),
            $methodName
        )->willReturn($returns);
    }

    private function mockInspectorSignatureByClassName($className, $methodName, $returns)
    {
        $this->inspector->getSignatureByClassName(
            $className,
            $methodName
        )->willReturn($returns);
    }
}
