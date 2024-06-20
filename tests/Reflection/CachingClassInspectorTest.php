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
use ReflectionException;
use Tests\Dummy\DummyDependency;

class CachingClassInspectorTest extends TestCase
{
    use ProphecyTrait;

    private CachingClassInspector $subject;
    private ArrayServiceCache|ObjectProphecy $serviceCache;
    private ClassInspector|ObjectProphecy $classInspector;

    protected function setUp(): void
    {
        parent::setUp();
        $serviceCache = $this->prophesize(ServiceCacheInterface::class);
        $serviceCache->set(Argument::cetera())->will(function ($arguments) use ($serviceCache) {
            $serviceCache->get($arguments[0])->willReturn($arguments[1]);
        });
        $this->serviceCache = $serviceCache;
        $this->classInspector = $this->prophesize(ClassInspector::class);
        $this->subject = new CachingClassInspector(
            $this->classInspector->reveal(),
            $this->serviceCache->reveal(),
        );
    }

    public function testWarmCachePopulatesCaches(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->classInspector->classHasMethod(DummyDependency::class, 'isEnabled')->willReturn(true);
        $this->classInspector->methodIsPublic(DummyDependency::class, 'isEnabled')->willReturn(true);
        $this->classInspector->getMethodSignature(DummyDependency::class, 'isEnabled')->willReturn([]);

        $this->subject->warmCache(DummyDependency::class, 'isEnabled');

        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled::exists", true)
            ->shouldHaveBeenCalled();
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled::is_public", true)
            ->shouldHaveBeenCalled();
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled::signature", [])
            ->shouldHaveBeenCalled();
    }

    public function testClassHasMethodUsesCachedResult(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(true);
        $this->serviceCache->get(Argument::cetera())->willReturn(true);

        $this->subject->classHasMethod(DummyDependency::class, 'isEnabled');

        $this->classInspector->classHasMethod(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    public function testClassHasMethodReturnsTrueForExistingMethodOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->classInspector->classHasMethod(DummyDependency::class, 'isEnabled')->willReturn(true);

        $hasMethod = $this->subject->classHasMethod(DummyDependency::class, 'isEnabled');

        $this->assertTrue($hasMethod);
    }

    public function testClassHasMethodReturnsFalseForMissingMethodOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->classInspector->classHasMethod(DummyDependency::class, 'isEnabled2')->willReturn(false);

        $hasMethod = $this->subject->classHasMethod(DummyDependency::class, 'isEnabled2');

        $this->assertFalse($hasMethod);
    }

    public function testClassHasMethodAddsResultToCacheOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->classInspector->classHasMethod(DummyDependency::class, 'isEnabled')->willReturn(true);

        $this->subject->classHasMethod(DummyDependency::class, 'isEnabled');

        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled::exists", true)
            ->shouldHaveBeenCalled();
    }

    public function testMethodIsPublicUsesCachedResult(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(true);
        $this->serviceCache->get(Argument::cetera())->willReturn(true);

        $this->subject->methodIsPublic(DummyDependency::class, 'isEnabled');

        $this->classInspector->methodIsPublic(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    public function testMethodIsPublicReturnsTrueForExistingMethodOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->classInspector->methodIsPublic(DummyDependency::class, 'isEnabled')->willReturn(true);

        $hasMethod = $this->subject->methodIsPublic(DummyDependency::class, 'isEnabled');

        $this->assertTrue($hasMethod);
    }

    public function testMethodIsPublicThrowsExceptionForMissingMethodOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled2::is_public", false);
        $this->classInspector->methodIsPublic(DummyDependency::class, 'isEnabled2')->willThrow(ReflectionException::class);

        $this->expectException(ReflectionException::class);
        $this->subject->methodIsPublic(DummyDependency::class, 'isEnabled2');
    }

    public function testMethodIsPublicAddsResultToCacheOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->classInspector->methodIsPublic(DummyDependency::class, 'isEnabled')->willReturn(true);

        $this->subject->methodIsPublic(DummyDependency::class, 'isEnabled');

        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled::is_public", true)
            ->shouldHaveBeenCalled();
    }

    public function testGetMethodSignatureUsesCachedResult(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(true);
        $this->serviceCache->get(Argument::cetera())->willReturn([]);

        $this->subject->getMethodSignature(DummyDependency::class, 'isEnabled');

        $this->classInspector->getMethodSignature(Argument::cetera())->shouldNotHaveBeenCalled();
    }


    public function testGetMethodSignatureReturnsSignatureForExistingMethodOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->classInspector->getMethodSignature(Argument::any(), 'isEnabled')->willReturn([]);

        $signature = $this->subject->getMethodSignature(DummyDependency::class, 'isEnabled');

        $this->assertEquals([], $signature);
    }

    public function testGetMethodSignatureThrowsExceptionForMissingMethodOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->classInspector->getMethodSignature(Argument::any(), 'isEnabled2')->willThrow(ReflectionException::class);

        $this->expectException(ReflectionException::class);
        $this->subject->getMethodSignature(DummyDependency::class, 'isEnabled2');
    }

    public function testGetMethodSignatureAddsResultToCacheOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->classInspector->getMethodSignature(Argument::any(), 'isEnabled')->willReturn([]);

        $this->subject->getMethodSignature(DummyDependency::class, 'isEnabled');

        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled::signature", [])
            ->shouldHaveBeenCalled();
    }
}
