<?php

declare(strict_types=1);

namespace Tests\Reflection;

use Bigcommerce\Injector\Reflection\ReflectionClassCache;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use Tests\Dummy\DummyDependency;
use Tests\Dummy\DummyNoConstructor;
use Tests\Dummy\DummySimpleConstructor;

class ReflectionClassCacheTest extends TestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testHasReturnsTrueWhenEntryExistsForGivenReflectionClass(): void
    {
        $subject = new ReflectionClassCache(10);
        $subject->put(new ReflectionClass(DummyDependency::class));

        $result = $subject->has(DummyDependency::class);

        $this->assertTrue($result);
    }

    public function testCountReturnsTotalNumberOfCacheEntries(): void
    {
        $subject = new ReflectionClassCache(10);
        $subject->put(new ReflectionClass(DummyDependency::class));
        $subject->put(new ReflectionClass(DummyNoConstructor::class));

        $result = $subject->count();

        $this->assertEquals(2, $result);
    }

    public function testGetReturnsReflectionClassForGivenClassIfPresent(): void
    {
        $subject = new ReflectionClassCache(10);
        $subject->put(new ReflectionClass(DummyDependency::class));

        $result = $subject->get(DummyDependency::class);

        $this->assertEquals(DummyDependency::class, $result->getName());
    }

    public function testPutAddsItemToCache(): void
    {
        $subject = new ReflectionClassCache(10);
        $subject->put(new ReflectionClass(DummyDependency::class));

        $result = $subject->count();

        $this->assertEquals(1, $result);
    }

    public function testCacheDoesNotExceedMaxSize(): void
    {
        $subject = new ReflectionClassCache(2);
        $subject->put(new ReflectionClass(DummyDependency::class));
        $subject->put(new ReflectionClass(DummyNoConstructor::class));
        $subject->put(new ReflectionClass(DummySimpleConstructor::class));

        $result = $subject->count();

        $this->assertEquals(2, $result);
    }
}
