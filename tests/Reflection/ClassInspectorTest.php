<?php

declare(strict_types=1);

namespace Tests\Reflection;

use Bigcommerce\Injector\Cache\ArrayServiceCache;
use Bigcommerce\Injector\Reflection\ClassInspector;
use Bigcommerce\Injector\Reflection\ClassInspectorStats;
use Bigcommerce\Injector\Reflection\ParameterInspector;
use Bigcommerce\Injector\Reflection\ReflectionClassMap;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionException;
use Tests\Dummy\DummyDependency;
use ReflectionClass;
use Tests\Dummy\DummyPrivateConstructor;

class ClassInspectorTest extends TestCase
{
    use ProphecyTrait;

    private ClassInspector $subject;
    private ArrayServiceCache|ObjectProphecy $serviceCache;
    private ReflectionClassMap|ObjectProphecy $reflectionClassMap;
    private ParameterInspector|ObjectProphecy $parameterInspector;
    private ClassInspectorStats|ObjectProphecy $classInspectorStats;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serviceCache = $this->prophesize(ArrayServiceCache::class);
        $this->reflectionClassMap = $this->prophesize(ReflectionClassMap::class);
        $this->parameterInspector = $this->prophesize(ParameterInspector::class);
        $this->classInspectorStats = $this->prophesize(ClassInspectorStats::class);
        $this->subject = new ClassInspector(
            $this->reflectionClassMap->reveal(),
            $this->parameterInspector->reveal(),
            $this->serviceCache->reveal(),
            $this->classInspectorStats->reveal(),
        );
    }

    public function testInspectMethodPopulatesCaches(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_exists", true);
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_is_public", true);
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_signature", []);
        $this->reflectionClassMap->has(Argument::cetera())->willReturn(false);
        $this->reflectionClassMap->put(Argument::cetera());
        $this->parameterInspector->getSignatureByReflectionClass(Argument::type(ReflectionClass::class), 'isEnabled')->willReturn([]);

        $this->subject->inspectMethod(DummyDependency::class, 'isEnabled');

        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_exists", true)
            ->shouldHaveBeenCalled();
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_is_public", true)
            ->shouldHaveBeenCalled();
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_signature", [])
            ->shouldHaveBeenCalled();
        $this->reflectionClassMap->put(Argument::type(ReflectionClass::class))
            ->shouldHaveBeenCalled();
    }

    public function testClassHasMethodUsesCachedResult(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(true);
        $this->serviceCache->get(Argument::cetera())->willReturn(true);

        $this->subject->classHasMethod(DummyDependency::class, 'isEnabled');

        $this->classInspectorStats->incrementReflectionClassesCreated()->shouldNotHaveBeenCalled();
    }

    public function testClassHasMethodReturnsTrueForExistingMethodOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_exists", true);
        $this->reflectionClassMap->has(Argument::cetera())->willReturn(false);
        $this->reflectionClassMap->put(Argument::type(ReflectionClass::class));

        $hasMethod = $this->subject->classHasMethod(DummyDependency::class, 'isEnabled');

        $this->assertTrue($hasMethod);
    }

    public function testClassHasMethodReturnsFalseForMissingMethodOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled2_exists", false);
        $this->reflectionClassMap->has(Argument::cetera())->willReturn(false);
        $this->reflectionClassMap->put(Argument::type(ReflectionClass::class));

        $hasMethod = $this->subject->classHasMethod(DummyDependency::class, 'isEnabled2');

        $this->assertFalse($hasMethod);
    }

    public function testClassHasMethodAddsResultToCacheOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_exists", true);
        $this->reflectionClassMap->has(Argument::cetera())->willReturn(false);
        $this->reflectionClassMap->put(Argument::type(ReflectionClass::class));

        $this->subject->classHasMethod(DummyDependency::class, 'isEnabled');

        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_exists", true)
            ->shouldHaveBeenCalled();
    }

    public function testMethodIsPublicUsesCachedResult(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(true);
        $this->serviceCache->get(Argument::cetera())->willReturn(true);

        $this->subject->methodIsPublic(DummyDependency::class, 'isEnabled');

        $this->classInspectorStats->incrementReflectionClassesCreated()->shouldNotHaveBeenCalled();
    }

    public function testMethodIsPublicReturnsTrueForExistingMethodOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_is_public", true);
        $this->reflectionClassMap->has(Argument::cetera())->willReturn(false);
        $this->reflectionClassMap->put(Argument::type(ReflectionClass::class));

        $hasMethod = $this->subject->methodIsPublic(DummyDependency::class, 'isEnabled');

        $this->assertTrue($hasMethod);
    }

    public function testMethodIsPublicThrowsExceptionForMissingMethodOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled2_is_public", false);
        $this->reflectionClassMap->has(Argument::cetera())->willReturn(false);
        $this->reflectionClassMap->put(Argument::type(ReflectionClass::class));

        $this->expectException(ReflectionException::class);
        $this->subject->methodIsPublic(DummyDependency::class, 'isEnabled2');
    }

    public function testMethodIsPublicReturnsFalseForPrivateMethodOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->serviceCache->set("Tests\Dummy\DummyPrivateConstructor::__construct_is_public", false);
        $this->reflectionClassMap->has(Argument::cetera())->willReturn(false);
        $this->reflectionClassMap->put(Argument::type(ReflectionClass::class));

        $isPublic = $this->subject->methodIsPublic(DummyPrivateConstructor::class, '__construct');

        $this->assertFalse($isPublic);
    }

    public function testMethodIsPublicAddsResultToCacheOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_is_public", true);
        $this->reflectionClassMap->has(Argument::cetera())->willReturn(false);
        $this->reflectionClassMap->put(Argument::type(ReflectionClass::class));

        $this->subject->methodIsPublic(DummyDependency::class, 'isEnabled');

        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_is_public", true)
            ->shouldHaveBeenCalled();
    }

    public function testGetMethodSignatureUsesCachedResult(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(true);
        $this->serviceCache->get(Argument::cetera())->willReturn([]);

        $this->subject->getMethodSignature(DummyDependency::class, 'isEnabled');

        $this->classInspectorStats->incrementReflectionClassesCreated()->shouldNotHaveBeenCalled();
    }


    public function testGetMethodSignatureReturnsSignatureForExistingMethodOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_signature", []);
        $this->reflectionClassMap->has(Argument::cetera())->willReturn(false);
        $this->reflectionClassMap->put(Argument::type(ReflectionClass::class));
        $this->parameterInspector->getSignatureByReflectionClass(Argument::any(), 'isEnabled')->willReturn([]);

        $signature = $this->subject->getMethodSignature(DummyDependency::class, 'isEnabled');

        $this->assertEquals([], $signature);
    }

    public function testGetMethodSignatureThrowsExceptionForMissingMethodOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->reflectionClassMap->has(Argument::cetera())->willReturn(false);
        $this->reflectionClassMap->put(Argument::type(ReflectionClass::class));
        $this->parameterInspector->getSignatureByReflectionClass(Argument::any(), 'isEnabled2')->willThrow(new ReflectionException());

        $this->expectException(ReflectionException::class);
        $this->subject->getMethodSignature(DummyDependency::class, 'isEnabled2');
    }

    public function testGetMethodSignatureAddsResultToCacheOnCacheMiss(): void
    {
        $this->serviceCache->has(Argument::cetera())->willReturn(false);
        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_signature", []);
        $this->reflectionClassMap->has(Argument::cetera())->willReturn(false);
        $this->reflectionClassMap->put(Argument::type(ReflectionClass::class));
        $this->parameterInspector->getSignatureByReflectionClass(Argument::any(), 'isEnabled')->willReturn([]);

        $this->subject->getMethodSignature(DummyDependency::class, 'isEnabled');

        $this->serviceCache->set("Tests\Dummy\DummyDependency::isEnabled_signature", [])
            ->shouldHaveBeenCalled();
    }
}
