<?php
namespace Tests;

use Bigcommerce\Injector\Injector;
use Pimple\Container;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 *
 * @coversDefaultClass Bigcommerce\Injector\ServiceProvider
 */
class ServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var Injector|ObjectProphecy */
    private $injector;

    /** @var Container|ObjectProphecy */
    private $container;

    public function setUp()
    {
        parent::setUp();
        $this->injector = $this->prophesize(Injector::class);
        $this->container = $this->prophesize(Container::class);
    }

    public function testSample()
    {
        $this->container->offsetSet("test1", "123")->shouldBeCalledTimes(1);
        $this->container->offsetGet("test1")->shouldBeCalledTimes(1);
        // Factory will be called twice (once directly, and once in factory autobind)
        $this->container->factory(Argument::any())->shouldBeCalledTimes(2)->willReturn("fish");
        // Alias will be called once, and pass the result from a factory call
        $this->container->offsetSet("alias", "fish")->shouldBeCalledTimes(1);
        // Create should pass through to injector
        $this->injector->create(SampleProvider::class, ["abc" => 123])->shouldBeCalledTimes(1);
        // Autobind should bind a closure (once for factory, once for service)
        $this->container->offsetSet(SampleProvider::class, Argument::any())->shouldBeCalledTimes(2);

        $provider = new SampleProvider($this->injector->reveal(), $this->container->reveal());

        $provider->register($this->container->reveal());
    }

}
