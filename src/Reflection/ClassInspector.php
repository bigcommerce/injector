<?php

declare(strict_types=1);

namespace Bigcommerce\Injector\Reflection;

use ReflectionClass;
use ReflectionException;

class ClassInspector implements ClassInspectorInterface
{
    public function __construct(
        private readonly ReflectionClassCache $reflectionClassCache,
        private readonly ParameterInspector $parameterInspector,
        private readonly ClassInspectorStats $stats
    ) {
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
        return $this->getReflectionClass($class)->hasMethod($method);
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
        return $this->getReflectionClass($class)->getMethod($method)->isPublic();
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
        $reflectionClass = $this->getReflectionClass($class);

        return $this->parameterInspector->getSignatureByReflectionClass($reflectionClass, $method);
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
