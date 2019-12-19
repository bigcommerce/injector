<?php
namespace Tests\ServiceProvider;

use Bigcommerce\Injector\Injector;
use Bigcommerce\Injector\ServiceProvider\BindingClosureFactory;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Prophecy\Prophecy\ObjectProphecy;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\ValueHolderInterface;

/**
 *
 * @coversDefaultClass \Bigcommerce\Injector\ServiceProvider\BindingClosureFactory
 */
class BindingClosureFactoryTest extends TestCase
{
    /** @var Injector|ObjectProphecy */
    private $injector;

    /** @var Container|ObjectProphecy */
    private $container;

    /** @var LazyLoadingValueHolderFactory|ObjectProphecy */
    private $proxyFactory;

    public function setUp(): void
    {
        parent::setUp();
        $this->injector = $this->prophesize(Injector::class);
        $this->container = $this->prophesize(Container::class);
        $this->proxyFactory = $this->prophesize(LazyLoadingValueHolderFactory::class);
    }

    /**
     * @covers ::createAutoWireClosure
     */
    public function testCreateAutoWireClosure()
    {
        $this->injector->create(self::class, [])->willReturn(123)->shouldBeCalledTimes(1);
        $closureFactory = new BindingClosureFactory($this->proxyFactory->reveal(), $this->injector->reveal());

        $closure = $closureFactory->createAutoWireClosure(self::class);

        $result = $closure($this->container->reveal());

        $this->assertEquals(123, $result);
    }

    /**
     * @covers ::createAutoWireClosure
     */
    public function testCreateAutoWireClosureWithParameters()
    {
        $parameterFactory = function (Container $app) {
            return ["test" => "abc"];
        };

        $this->injector->create(self::class, ["test" => "abc"])->willReturn(123)->shouldBeCalledTimes(1);
        $closureFactory = new BindingClosureFactory($this->proxyFactory->reveal(), $this->injector->reveal());

        $closure = $closureFactory->createAutoWireClosure(self::class, $parameterFactory);

        $result = $closure($this->container->reveal());

        $this->assertEquals(123, $result);
    }

    /**
     * Note: This is an integration test - as we're interacting with a boundary library (ocramius/proxy-manager) we're
     * asserting that the proxy returned behaves per our own contract requires (collaboration test).
     *
     * @return void
     */
    public function testCreateAutoWireProxyClosure()
    {
        $this->injector->create(LazyDummy::class, [])->willReturn(new LazyDummy("bob"))->shouldBeCalledTimes(1);

        $closureFactory = new BindingClosureFactory(new LazyLoadingValueHolderFactory(), $this->injector->reveal());

        $closure = $closureFactory->createAutoWireProxyClosure(LazyDummy::class);
        /** @var ValueHolderInterface|LazyDummy $proxy */
        $proxy = $closure($this->container->reveal());

        $this->assertEquals("bob", $proxy->getName());
    }
}
