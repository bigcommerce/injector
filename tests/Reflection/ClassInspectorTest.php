<?php

declare(strict_types=1);

namespace Tests\Reflection;

use _PHPStan_dcc7b7cff\Nette\Utils\Reflection;
use Bigcommerce\Injector\Cache\ArrayServiceCache;
use Bigcommerce\Injector\Reflection\ClassInspector;
use Bigcommerce\Injector\Reflection\ClassInspectorStats;
use Bigcommerce\Injector\Reflection\ParameterInspector;
use Bigcommerce\Injector\Reflection\ReflectionClassCache;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionException;
use Tests\Dummy\DummyDependency;
use ReflectionClass;
use Tests\Dummy\DummyPrivateConstructor;
use Tests\Dummy\DummySubDependency;

class ClassInspectorTest extends TestCase
{
    use ProphecyTrait;

    private ClassInspector $subject;
    private ReflectionClassCache|ObjectProphecy $reflectionClassCache;
    private ParameterInspector|ObjectProphecy $parameterInspector;
    private ClassInspectorStats $classInspectorStats;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflectionClassCache = $this->prophesize(ReflectionClassCache::class);
        $this->reflectionClassCache->has(Argument::cetera())->willReturn(false);
        $this->reflectionClassCache->get(Argument::cetera())->willReturn(null);
        $this->parameterInspector = $this->prophesize(ParameterInspector::class);
        $this->classInspectorStats = new ClassInspectorStats();
        $this->subject = new ClassInspector(
            $this->reflectionClassCache->reveal(),
            $this->parameterInspector->reveal(),
            $this->classInspectorStats,
        );
    }

    public function testClassHasMethodReturnsTrueForPublicMethod(): void
    {
        $this->reflectionClassCache->put(Argument::cetera());

        $hasMethod = $this->subject->classHasMethod(DummyDependency::class, 'isEnabled');

        $this->assertTrue($hasMethod);
    }

    public function testClassHasMethodReturnsFalseForMissingMethod(): void
    {
        $this->reflectionClassCache->put(Argument::cetera());

        $hasMethod = $this->subject->classHasMethod(DummyDependency::class, 'missingMethod');

        $this->assertFalse($hasMethod);
    }

    public function testMethodIsPublicReturnsTrueForPublicMethod(): void
    {
        $this->reflectionClassCache->put(Argument::cetera());

        $isPublic = $this->subject->methodIsPublic(DummyDependency::class, 'isEnabled');

        $this->assertTrue($isPublic);
    }

    public function testMethodIsPublicReturnsFalseForPrivateMethod(): void
    {
        $this->reflectionClassCache->put(Argument::cetera());

        $isPublic = $this->subject->methodIsPublic(DummyPrivateConstructor::class, '__construct');

        $this->assertFalse($isPublic);
    }

    public function testMethodIsPublicThrowsExceptionForMissingMethod(): void
    {
        $this->reflectionClassCache->put(Argument::cetera());

        $class = DummyDependency::class;
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage("Method $class::missingMethod() does not exist");
        $this->subject->methodIsPublic($class, 'missingMethod');
    }

    public function testGetMethodSignatureReturnsSignatureForPublicMethod(): void
    {
        $this->reflectionClassCache->put(Argument::cetera());
        $this->parameterInspector->getSignatureByReflectionClass(Argument::type(ReflectionClass::class), '__construct')
            ->willReturn([]);

        $signature = $this->subject->getMethodSignature(DummySubDependency::class, '__construct');

        $this->assertEquals([], $signature);
    }

    public function testGetMethodSignatureReturnsSignatureForPrivateMethod(): void
    {
        $this->reflectionClassCache->put(Argument::cetera());
        $this->parameterInspector->getSignatureByReflectionClass(Argument::type(ReflectionClass::class), '__construct')
            ->willReturn(
                [
                    'name' => '__construct',
                    'type' => 'string',
                ],
            );

        $signature = $this->subject->getMethodSignature(DummyPrivateConstructor::class, '__construct');

        $this->assertEquals(
            [
                'name' => '__construct',
                'type' => 'string',
            ],
            $signature,
        );
    }

    public function testGetMethodSignatureThrowsExceptionForMissingMethod(): void
    {
        $this->reflectionClassCache->put(Argument::cetera());

        $this->parameterInspector->getSignatureByReflectionClass(Argument::type(ReflectionClass::class), 'missingMethod')
            ->willThrow(ReflectionException::class);

        $class = DummyPrivateConstructor::class;
        $this->expectException(ReflectionException::class);
        $this->subject->getMethodSignature($class, 'missingMethod');
    }

    public function testClassInspectorRecordsStatsForReflectionClassesCreated(): void
    {
        $this->reflectionClassCache->put(Argument::cetera())->shouldBeCalled();

        $this->subject->classHasMethod(DummyDependency::class, 'isEnabled');

        $this->assertEquals(1, $this->subject->getStats()->getReflectionClassesCreated());
    }
}
