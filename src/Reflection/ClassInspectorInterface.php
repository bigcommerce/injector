<?php

declare(strict_types=1);

namespace Bigcommerce\Injector\Reflection;

use ReflectionException;

/**
 * @internal
 */
interface ClassInspectorInterface
{
    /**
     * @template T of object
     * @param string $class
     * @param class-string<T> $class
     * @param string $method
     * @return bool The reflection class instance for the given class.
     * @throws ReflectionException
     */
    public function classHasMethod(string $class, string $method): bool;

    /**
     * @template T of object
     * @param string $class
     * @param class-string<T> $class
     * @param string $method
     * @return bool The reflection class instance for the given class.
     * @throws ReflectionException
     */
    public function methodIsPublic(string $class, string $method): bool;

    /**
     * @template T of object
     * @param string $class
     * @param class-string<T> $class
     * @param string $method
     * @return array{'name': string, 'type'?: string, 'default'?: mixed, 'variadic'?: bool}[]
     * @throws ReflectionException
     */
    public function getMethodSignature(string $class, string $method): array;

    /**
     * Returns null if the class has no constructor
     * Returns false if the constructor is not public
     * Returns the constructor signature array if the constructor is public
     *
     * @param string $class
     * @return array|false|null
     * @throws ReflectionException
     */
    public function getCallableConstructorSignature(string $class): array|false|null;

    public function getStats(): ClassInspectorStats;
}
