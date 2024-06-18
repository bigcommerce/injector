<?php

declare(strict_types=1);

namespace Bigcommerce\Injector\Reflection;

use Bigcommerce\Injector\Cache\ServiceCacheInterface;
use ReflectionClass;
use ReflectionException;

class ClassInspector
{
    public function __construct(
        private readonly ReflectionClassCache $reflectionClassCache,
        private readonly ParameterInspector $parameterInspector,
        private readonly ServiceCacheInterface $serviceCache,
        private readonly ClassInspectorStats $stats
    ) {
    }

    /**
     * @param string $class
     * @param string $method
     * @return void
     * @throws ReflectionException
     */
    public function inspectMethod(string $class, string $method): void
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
            return $this->serviceCache->get($key);
        }

        $methodExists = $this->getReflectionClass($class)->hasMethod($method);
        $this->serviceCache->set($key, $methodExists);

        return $methodExists;
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
            return $this->serviceCache->get($key);
        }

        $methodIsPublic = $this->getReflectionClass($class)->getMethod($method)->isPublic();
        $this->serviceCache->set($key, $methodIsPublic);

        return $methodIsPublic;
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
            return $this->serviceCache->get($key);
        }

        $reflectionClass = $this->getReflectionClass($class);
        $methodSignature = $this->parameterInspector->getSignatureByReflectionClass($reflectionClass, $method);
        $this->serviceCache->set($key, $methodSignature);

        return $methodSignature;
    }

    public function getStats(): ClassInspectorStats
    {
        return $this->stats;
    }

    /**
     * Gets a ReflectionClass instance for the given class name.
     *
     * @template T of object
     * @param string $class The name of the class to reflect.
     * @phpstan-param class-string<T> $class The name of the class to reflect.
     * @return ReflectionClass<T> The reflection class instance for the given class.
     * @throws ReflectionException
     */
    private function getReflectionClass(string $class): ReflectionClass
    {
        if ($this->reflectionClassCache->has($class)) {
            $reflectionClass = $this->reflectionClassCache->get($class);
        } else {
            $reflectionClass = new ReflectionClass($class);
            $this->stats->incrementReflectionClassesCreated();
            $this->reflectionClassCache->put($reflectionClass);
        }

        return $reflectionClass;
    }
}
