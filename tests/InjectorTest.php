<?php
namespace Tests;

use Bigcommerce\Injector\Adapter\ArrayContainerAdapter;
use Bigcommerce\Injector\Exception\InjectorInvocationException;
use Bigcommerce\Injector\FindableContainerInterface;
use Bigcommerce\Injector\Injector;
use Bigcommerce\Injector\Reflection\ClassInspector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Pimple\Container as PimpleContainer;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Tests\Dummy\DummyDependency;
use Tests\Dummy\DummyNoConstructor;
use Tests\Dummy\DummyNullableDependency;
use Tests\Dummy\DummyPrivateConstructor;
use Tests\Dummy\DummySimpleConstructor;
use Tests\Dummy\DummyString;
use Tests\Dummy\DummySubDependency;
use Tests\Dummy\DummyVariadicConstructor;
use TypeError;

#[CoversClass(Injector::class)]
class InjectorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ContainerInterface|ObjectProphecy
     */
    private $container;

    /**
     * @var ClassInspector|ObjectProphecy
     */
    private $inspector;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->inspector = $this->prophesize(ClassInspector::class);
    }

    public function testCreateNoConstructor()
    {
        $this->inspector->getCallableConstructorSignature(DummyNoConstructor::class)->willReturn(null);
        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $instance = $injector->create(DummyNoConstructor::class);
        $this->assertInstanceOf(DummyNoConstructor::class, $instance);
    }

    public function testCreatePrivateConstructor()
    {
        $this->inspector->getCallableConstructorSignature(DummyPrivateConstructor::class)->willReturn(false);
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
        $dummyNoConstructor = $this->prophesize(DummyNoConstructor::class)->reveal();
        $dummyDependency = new DummyDependency(new DummySubDependency());

        $this->mockDummySimpleSignature();

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        /** @var DummySimpleConstructor $instance */
        $instance = $injector->create(
            DummySimpleConstructor::class,
            [
                "dummyNoConstructor" => $dummyNoConstructor, //Parameter Name
                DummyDependency::class => $dummyDependency, //Parameter Type
                2 => "bob" //Parameter Index
                //Missing value - 'age' should use its default
            ]
        );
        $this->assertSame($dummyNoConstructor, $instance->getDummyNoConstructor());
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
        $dummyNoConstructor = $this->prophesize(DummyNoConstructor::class)->reveal();
        $dummyDependency = new DummyDependency(new DummySubDependency());
        $this->mockDummySimpleSignature();

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        /** @var DummySimpleConstructor $instance */
        $instance = $injector->create(
            DummySimpleConstructor::class,
            [
                "dummyNoConstructor" => $dummyNoConstructor, //Parameter Name
                DummyDependency::class => $dummyDependency, //Parameter Type
                2 => "bob", //Parameter Index
                3 => 10, //Prameter Index
                'hello', //Variadic
                'world',
            ]
        );

        $this->assertSame($dummyNoConstructor, $instance->getDummyNoConstructor());
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
        $dummyNoConstructor = $this->prophesize(DummyNoConstructor::class)->reveal();
        $dummyDependency = new DummyDependency(new DummySubDependency());

        $this->mockDummySimpleSignature();

        $this->container->has(DummyNoConstructor::class)->willReturn(true);
        $this->container->get(DummyNoConstructor::class)->willReturn($dummyNoConstructor);
        $this->container->has(DummyDependency::class)->willReturn(true);
        $this->container->get(DummyDependency::class)->willReturn($dummyDependency);

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        /** @var DummySimpleConstructor $instance */
        $instance = $injector->create(
            DummySimpleConstructor::class,
            [
                //Missing value - 'dummyNoConstructor' should come from container
                //Missing value - 'dummyDependency' should come from container
                2 => "bob" //Parameter Index
                //Missing value - 'age' should use its default
            ]
        );
        $this->assertSame($dummyNoConstructor, $instance->getDummyNoConstructor());
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
        $dummyNoConstructor = $this->prophesize(DummyNoConstructor::class)->reveal();

        $this->mockDummySimpleSignature();
        $this->mockDummyDependencySignature();
        $this->mockDummySubDependencySignature();

        $this->container->has(Argument::any())->willReturn(false);
        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $injector->addAutoCreate(".*?Dummy.*?");
        $instance = $injector->create(
            DummySimpleConstructor::class,
            [
                DummyNoConstructor::class => $dummyNoConstructor,
                //Missing value - 'dummyDependency' should come from auto-create
                2 => "bob" //Parameter Index
                //Missing value - 'age' should use its default
            ]
        );
        $this->assertSame($dummyNoConstructor, $instance->getDummyNoConstructor());
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
        $dummyNoConstructor = $this->prophesize(DummyNoConstructor::class)->reveal();

        $this->mockDummySimpleSignature();
        $this->mockDummyDependencySignature();

        $this->container->has(Argument::any())->willReturn(false);
        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $injector->addAutoCreate(".*?DummyDependency");
        $instance = $injector->create(
            DummySimpleConstructor::class,
            [
                DummyNoConstructor::class => $dummyNoConstructor,
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
            '/missing parameter \'\$dummyNoConstructor \[' . addslashes(DummyNoConstructor::class) . '\]\'/ims'
        );
        $this->mockDummySimpleSignature();

        $this->container->has(Argument::any())->willReturn(false);
        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $instance = $injector->create(
            DummySimpleConstructor::class
        );
    }

    public function testInvokeOnNonObject()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Attempted Injector::invoke on a non-object: array.");
        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        //We're intentionally passing the wrong type to invoke here to assert the failure.
        /** @noinspection PhpParamsInspection */
        $injector->invoke([], "__construct");
    }

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

    public function testInvokeInvalidMethod()
    {
        $this->expectException(InjectorInvocationException::class);
        $messageContains = [
            'Failed to invoke ' . addslashes(DummyNoConstructor::class) . '::setName - method doesn\'t exist.'
        ];
        $this->expectExceptionMessageMatches("/" . implode(".*?", $messageContains) . "/ims");
        $this->inspector->getMethodSignature(DummyNoConstructor::class, "setName")->willThrow(
            new \ReflectionException("bad stuff")
        );

        $injector = new Injector($this->container->reveal(), $this->inspector->reveal());
        $instance = new DummyNoConstructor();
        $injector->invoke($instance, "setName", []);
    }

    private function mockDummyDependencySignature()
    {
        $this->inspector->getCallableConstructorSignature(DummyDependency::class)
            ->willReturn([
                ["name" => "dependency", "type" => DummySubDependency::class],
                ["name" => "enabled", "default" => true]
            ]);
    }

    private function mockDummySubDependencySignature()
    {
        $this->inspector->getCallableConstructorSignature(DummySubDependency::class)
            ->willReturn([
                ["name" => "enabled", "default" => true]
            ]);
    }

    private function mockDummySimpleSignature()
    {
        $this->inspector->getCallableConstructorSignature(DummySimpleConstructor::class)
            ->willReturn([
                ["name" => "dummyNoConstructor", "type" => DummyNoConstructor::class],
                ["name" => "dummyDependency", "type" => DummyDependency::class],
                ["name" => "name"],
                ["name" => "age", "default" => 25],
                ["name" => "args", "variadic" => true],
            ]);
    }

    private function mockDummyVariadicSignature()
    {
        $this->inspector->getCallableConstructorSignature(DummyVariadicConstructor::class)
            ->willReturn([
                ["name" => "args", "variadic" => true],
            ]);
    }

    private function mockInspectorSignatureByClassName($className, $methodName, $returns)
    {
        $this->inspector->getMethodSignature(
            $className,
            $methodName
        )->willReturn($returns);
    }

    private function mockDummyNullableDependencySignature()
    {
        $this->inspector->getCallableConstructorSignature(DummyNullableDependency::class)
            ->willReturn([
                ["name" => "dependency", "type" => DummySubDependency::class],
            ]);
    }

    /**
     * The $useFindableContainer constructor flag defaults to false, so a container that implements
     * FindableContainerInterface must still be resolved via has()/get() unless the flag is explicitly enabled -
     * existing callers of Injector are unaffected until they opt in.
     */
    public function testFindableContainerIsNotUsedUnlessExplicitlyEnabled()
    {
        $dependency = new DummySubDependency();
        $this->mockDummyDependencySignature();

        $findable = $this->prophesize(FindableContainerInterface::class);
        $findable->has(DummySubDependency::class)->willReturn(true);
        $findable->get(DummySubDependency::class)->willReturn($dependency);

        $injector = new Injector($findable->reveal(), $this->inspector->reveal());
        $instance = $injector->create(DummyDependency::class);

        $this->assertSame($dependency, $instance->getDependency());
        $findable->find(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * With the flag enabled, a non-NULL resolution should use find() alone - has()/get() must not be called.
     */
    public function testFindableContainerFastPathResolvesNonNullValueWithoutSecondLookup()
    {
        $dependency = new DummySubDependency();
        $this->mockDummyDependencySignature();

        $findable = $this->prophesize(FindableContainerInterface::class);
        $findable->find(DummySubDependency::class)->willReturn($dependency);

        $injector = new Injector($findable->reveal(), $this->inspector->reveal(), true);
        $instance = $injector->create(DummyDependency::class);

        $this->assertSame($dependency, $instance->getDependency());
        $findable->has(Argument::any())->shouldNotHaveBeenCalled();
        $findable->get(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * The regression this whole flag exists for: find() returning NULL is ambiguous between "not registered" and
     * "registered, value is NULL". A dependency that is genuinely bound to NULL must resolve to NULL, not be
     * treated as absent (which previously caused MissingRequiredParameterException in production for any
     * nullable dependency whose container binding legitimately resolved to NULL).
     */
    public function testFindableContainerFastPathDisambiguatesADependencyRegisteredAsNull()
    {
        $this->mockDummyNullableDependencySignature();

        $findable = $this->prophesize(FindableContainerInterface::class);
        $findable->find(DummySubDependency::class)->willReturn(null);
        $findable->has(DummySubDependency::class)->willReturn(true);

        $injector = new Injector($findable->reveal(), $this->inspector->reveal(), true);
        $instance = $injector->create(DummyNullableDependency::class);

        $this->assertNull($instance->getDependency());
        $findable->get(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * The other half of the disambiguation: find() returning NULL because the dependency is genuinely absent
     * (has() also false) must still fall through to auto-create / default / MissingRequiredParameterException,
     * exactly as it would via has()/get().
     */
    public function testFindableContainerFastPathTreatsTrulyAbsentDependencyAsNotFound()
    {
        $this->mockDummyDependencySignature();
        $this->mockDummySubDependencySignature();

        $findable = $this->prophesize(FindableContainerInterface::class);
        $findable->find(DummySubDependency::class)->willReturn(null);
        $findable->has(DummySubDependency::class)->willReturn(false);

        $injector = new Injector($findable->reveal(), $this->inspector->reveal(), true);
        $injector->addAutoCreate(".*?DummySubDependency");
        $instance = $injector->create(DummyDependency::class);

        $this->assertInstanceOf(DummySubDependency::class, $instance->getDependency());
    }

    /**
     * End-to-end reproduction of the real incident, using an actual Pimple container (not a mock) wrapped in
     * ArrayContainerAdapter, matching exactly how the app wires the container into the Injector. A service bound
     * to NULL must resolve as NULL through the fast path, not throw.
     */
    public function testFindableContainerFastPathViaRealPimpleContainerHandlesADependencyBoundToNull()
    {
        $pimple = new PimpleContainer();
        $pimple[DummySubDependency::class] = null;

        $this->mockDummyNullableDependencySignature();

        $injector = new Injector(new ArrayContainerAdapter($pimple), $this->inspector->reveal(), true);
        $instance = $injector->create(DummyNullableDependency::class);

        $this->assertNull($instance->getDependency());
    }

    public function testResolvesDependencyFromFindableContainer()
    {
        $dep = new DummySubDependency();
        $this->mockDummyDependencySignature();

        $injector = new Injector(
            new ArrayContainerAdapter(new PimpleContainer([DummySubDependency::class => $dep])),
            $this->inspector->reveal(),
            true
        );

        $instance = $injector->create(DummyDependency::class);
        $this->assertSame($dep, $instance->getDependency());
    }

    public function testFindableContainerAutoCreatesWhenDependencyAbsent()
    {
        $this->mockDummyDependencySignature();
        $this->mockDummySubDependencySignature();

        $injector = new Injector(new ArrayContainerAdapter(new PimpleContainer()), $this->inspector->reveal(), true);
        $injector->addAutoCreate(".*DummySubDependency");

        $instance = $injector->create(DummyDependency::class);
        $this->assertInstanceOf(DummySubDependency::class, $instance->getDependency());
    }

    public function testFindableContainerThrowsForMissingRequiredDependency()
    {
        $this->mockDummyDependencySignature();

        $injector = new Injector(new ArrayContainerAdapter(new PimpleContainer()), $this->inspector->reveal(), true);

        $this->expectException(InjectorInvocationException::class);
        $injector->create(DummyDependency::class);
    }
}
