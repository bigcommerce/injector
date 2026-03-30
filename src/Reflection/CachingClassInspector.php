<?php

declare(strict_types=1);

namespace Bigcommerce\Injector\Reflection;

use Bigcommerce\Injector\Cache\ServiceCacheInterface;
use ReflectionException;

class CachingClassInspector implements ClassInspectorInterface
{
    /**
     * @var array<string, array{0: array<string, mixed>[]|false|null}>
     */
    private array $constructorCache = [];

    public function __construct(
        private readonly ClassInspector $classInspector,
        private readonly ServiceCacheInterface $serviceCache,
    ) {
    }

    /**
     * Warm up the cache for the given class and method.
     *
     * @param string $class
     * @param string $method
     * @return void
     * @throws ReflectionException
     */
    public function warmCache(string $class, string $method): void
    {
        if ($method === '__construct') {
            $this->getCallableConstructorSignature($class);
            return;
        }
        if ($this->classHasMethod($class, $method)) {
            if ($this->methodIsPublic($class, $method)) {
                $this->getMethodSignature($class, $method);
            }
        }
    }

    /**
     * @template T of object
     * @param string $class
     * @param class-string<T> $class
     * @param string $method
     * @return bool The reflection class instance for the given class.
     * @throws ReflectionException
     */
    public function classHasMethod(string $class, string $method): bool
    {
        $key = "$class::{$method}::exists";
        if ($this->serviceCache->has($key)) {
            $value = $this->serviceCache->get($key);
        } else {
            $value = $this->classInspector->classHasMethod($class, $method);
            $this->serviceCache->set($key, $value);
        }

        return $value;
    }

    /**
     * @template T of object
     * @param string $class
     * @param class-string<T> $class
     * @param string $method
     * @return bool The reflection class instance for the given class.
     * @throws ReflectionException
     */
    public function methodIsPublic(string $class, string $method): bool
    {
        $key = "$class::{$method}::is_public";
        if ($this->serviceCache->has($key)) {
            $value = $this->serviceCache->get($key);
        } else {
            $value = $this->classInspector->methodIsPublic($class, $method);
            $this->serviceCache->set($key, $value);
        }

        return $value;
    }

    /**
     * @template T of object
     * @param string $class
     * @param class-string<T> $class
     * @param string $method
     * @return array{'name': string, 'type'?: string, 'default'?: mixed, 'variadic'?: bool}[]
     * @throws ReflectionException
     */
    public function getMethodSignature(string $class, string $method): array
    {
        $key = "$class::{$method}::signature";
        if ($this->serviceCache->has($key)) {
            $value = $this->serviceCache->get($key);
        } else {
            $value = $this->classInspector->getMethodSignature($class, $method);
            $this->serviceCache->set($key, $value);
        }

        return $value;
    }

    /**
     * @param string $class
     * @return array<string, mixed>[]|false|null
     */
    public function getCallableConstructorSignature(string $class): array|false|null
    {
        if (isset($this->constructorCache[$class])) {
            return $this->constructorCache[$class][0];
        }
        $result = $this->classInspector->getCallableConstructorSignature($class);
        $this->constructorCache[$class] = [$result];
        return $result;
    }

    public function getStats(): ClassInspectorStats
    {
        return $this->classInspector->getStats();
    }
}
