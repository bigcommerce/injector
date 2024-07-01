<?php

declare(strict_types=1);

namespace Bigcommerce\Injector\Reflection;

use Bigcommerce\Injector\Cache\ServiceCacheInterface;
use ReflectionException;

class CachingClassInspector implements ClassInspectorInterface
{
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

    public function getStats(): ClassInspectorStats
    {
        return $this->classInspector->getStats();
    }
}
