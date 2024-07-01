<?php

declare(strict_types=1);

namespace Reflection;

use Bigcommerce\Injector\Cache\ArrayServiceCache;
use Bigcommerce\Injector\Cache\ServiceCacheInterface;
use Bigcommerce\Injector\Reflection\CachingClassInspector;
use Bigcommerce\Injector\Reflection\ClassInspector;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Tests\Dummy\DummyDependency;

class NoOpCachingClassInspectorTest extends TestCase
{
    use ProphecyTrait;

    private CachingClassInspector $subject;
    private ArrayServiceCache|ObjectProphecy $serviceCache;
    private ClassInspector|ObjectProphecy $classInspector;

    protected function setUp(): void
    {
        parent::setUp();
        $serviceCache = $this->prophesize(ServiceCacheInterface::class);
        $serviceCache->has(Argument::cetera())->willReturn(false);
        $serviceCache->get(Argument::cetera())->willReturn(false);

        $this->serviceCache = $serviceCache;
        $this->classInspector = $this->prophesize(ClassInspector::class);
        $this->subject = new CachingClassInspector(
            $this->classInspector->reveal(),
            $this->serviceCache->reveal(),
        );
    }

    public function testClassHasMethodWorksWithNoOpCache(): void
    {
        $this->classInspector->classHasMethod(Argument::cetera())->willReturn(true);
        $this->serviceCache->set(Argument::cetera())->shouldBeCalled();

        $hasMethod = $this->subject->classHasMethod(DummyDependency::class, 'isEnabled');

        $this->assertTrue($hasMethod);
    }

    public function testMethodIsPublicWorksWithNoOpCache(): void
    {
        $this->classInspector->methodIsPublic(Argument::cetera())->willReturn(true);
        $this->serviceCache->set(Argument::cetera())->shouldBeCalled();

        $public = $this->subject->methodIsPublic(DummyDependency::class, 'isEnabled');

        $this->assertTrue($public);
    }

    public function testGetMethodSignatureWorksWithNoOpCache(): void
    {
        $this->classInspector->getMethodSignature(Argument::cetera())->willReturn([]);
        $this->serviceCache->set(Argument::cetera())->shouldBeCalled();

        $signature = $this->subject->getMethodSignature(DummyDependency::class, 'isEnabled');

        $this->assertEquals([], $signature);
    }
}
