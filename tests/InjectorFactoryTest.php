<?php

declare(strict_types=1);

namespace Tests;

use Bigcommerce\Injector\Cache\ArrayServiceCache;
use Bigcommerce\Injector\Injector;
use Bigcommerce\Injector\InjectorFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class InjectorFactoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ContainerInterface|ObjectProphecy
     */
    private $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryCanCreateAnInjector(): void
    {
        $injector = InjectorFactory::create($this->container->reveal());

        $this->assertInstanceOf(Injector::class, $injector);
    }

    public function testFactoryCanCreateAnInjectorWithASpecificReflectionCacheSize(): void
    {
        $injector = InjectorFactory::create($this->container->reveal(), 20);

        $this->assertInstanceOf(Injector::class, $injector);
    }

    public function testFactoryCanCreateAnInjectorWithASpecificServiceCache(): void
    {
        $injector = InjectorFactory::create($this->container->reveal(), serviceCache: new ArrayServiceCache());

        $this->assertInstanceOf(Injector::class, $injector);
    }
}
