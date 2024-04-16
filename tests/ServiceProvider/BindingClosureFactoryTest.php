<?php

namespace Tests\ServiceProvider;

use Bigcommerce\Injector\Injector;
use Bigcommerce\Injector\ServiceProvider\BindingClosureFactory;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\ValueHolderInterface;
use ProxyManager\Proxy\VirtualProxyInterface;

/**
 *
 * @coversDefaultClass \Bigcommerce\Injector\ServiceProvider\BindingClosureFactory
 */
class BindingClosureFactoryTest extends TestCase
{
    use ProphecyTrait;

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

    /**
     * Integration test - creates real proxies (using ocramius/proxy-manager) around service definitions
     * @return void
     */
    public function testCreateServiceProxy()
    {
        $closureFactory = new BindingClosureFactory(new LazyLoadingValueHolderFactory(), $this->injector->reveal());

        $proxy = $closureFactory->createServiceProxy($this->container->reveal(), 'lazy.dummy', LazyDummy::class);

        // Dummy service being proxied
        $this->container->offsetGet('lazy.dummy')->willReturn(new LazyDummy('Adam'));

        // We have a proxy
        $this->assertInstanceOf(VirtualProxyInterface::class, $proxy);
        $this->assertFalse($proxy->isProxyInitialized());
        // And it proxies the service in the container
        $this->assertEquals("Adam", $proxy->getName());
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * Integration test - creates real proxies (using ocramius/proxy-manager) around service definitions
     * @return void
     */
    public function testCreateServiceProxyFailsOnInvalidType()
    {
        $closureFactory = new BindingClosureFactory(new LazyLoadingValueHolderFactory(), $this->injector->reveal());

        $proxy = $closureFactory->createServiceProxy($this->container->reveal(), 'not.this.class', self::class);

        // Dummy service which isn't an instance of expected self::class
        $this->container->offsetGet('not.this.class')->willReturn(new LazyDummy('Wrong dummy'));

        // We have a proxy
        $this->assertInstanceOf(VirtualProxyInterface::class, $proxy);
        $this->assertFalse($proxy->isProxyInitialized());

        // And it proxies the service in the container
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Invalid proxied/lazy service definition");
        $proxy->name();
    }
}
