<?php
namespace Tests;

use Bigcommerce\Injector\Injector;
use Bigcommerce\Injector\ServiceProvider\BindingClosureFactory;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 *
 * @coversDefaultClass Bigcommerce\Injector\InjectorServiceProvider
 */
class InjectorServiceProviderTest extends TestCase
{
    use ProphecyTrait;

    /** @var Injector|ObjectProphecy */
    private $injector;

    /** @var Container|ObjectProphecy */
    private $container;

    /** @var BindingClosureFactory|ObjectProphecy */
    private $closureFactory;

    public function setUp(): void
    {
        parent::setUp();
        $this->injector = $this->prophesize(Injector::class);
        $this->container = $this->prophesize(Container::class);
        $this->closureFactory = $this->prophesize(BindingClosureFactory::class);
    }

    /**
     * Note: This test is complicated as we are exercising protected behaviour through a SampleProvider dummy.
     * @return void
     */
    public function testSampleProvider()
    {
        $noopClosure = function () {
            //Does nothing.
        };
        $this->container->offsetSet("test1", "123")->shouldBeCalledTimes(1);
        $this->container->offsetGet("test1")->shouldBeCalledTimes(1);
        // bindFactory, autoBindFactory, lazyBindFactory
        $this->container->factory(Argument::any())->shouldBeCalledTimes(3)->willReturn("fish");
        // alias
        $this->container->offsetSet("alias", "fish")->shouldBeCalledTimes(1);
        // Create should pass through to injector
        $this->injector->create(SampleProvider::class, ["abc" => 123])->shouldBeCalledTimes(1);
        // Autobind should bind a closure (autoBind, autoBindFactory, lazyBind, lazyBindFactory)
        $this->container->offsetSet(SampleProvider::class, Argument::any())->shouldBeCalledTimes(4);
        $this->closureFactory->createAutoWireClosure(SampleProvider::class, null)->willReturn($noopClosure)
            ->shouldBeCalledTimes(2);
        // We expect two proxy closures to be created
        $this->closureFactory->createAutoWireProxyClosure(SampleProvider::class, null)->willReturn($noopClosure)
            ->shouldBeCalledTimes(2);

        $provider = new SampleProvider(
            $this->injector->reveal(),
            $this->container->reveal(),
            $this->closureFactory->reveal()
        );

        $provider->register($this->container->reveal());
    }

}
