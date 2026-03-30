<?php
namespace Bigcommerce\Injector\Reflection;

use ReflectionMethod;
use ReflectionNamedType;

/**
 * The ParameterInspector exposes cached reflection of a methods signature enabling auto-wiring of dependencies within
 * the Injector. This class does expose the \ReflectionParameter or utilise Parameter value objects but
 * uses a fluffy 'array' return type. This is intentional as it is optimised to be serialised in cache and we
 * reflectively inspect potentially hundreds of classes in each request. It should be considered a service to the
 * service cache and any other use cases requiring method reflection should likely implement their own reflection
 * and interact directly with the \ReflectionClass\Method\Parameter objects themselves.
 * @package \Bigcommerce\Injector
 */
class ParameterInspector
{
    /**
     * Fetch the method signature of a method when we have already created a \ReflectionClass
     * @param \ReflectionClass<object> $reflectionClass
     * @param string $methodName
     * @return array{'name': string, 'type'?: string, 'default'?: mixed, 'variadic'?: bool}[]
     * @throws \ReflectionException
     */
    public function getSignatureByReflectionClass(\ReflectionClass $reflectionClass, $methodName)
    {
        $className = $reflectionClass->getName();
        return $this->getMethodSignature($className, $methodName, $reflectionClass);
    }

    /**
     * Fetch the method signature of a method using its fully qualified class name, and method name.
     * @param string $className
     * @param string $methodName
     * @return array{'name': string, 'type'?: string, 'default'?: mixed, 'variadic'?: bool}[]
     * @throws \ReflectionException
     */
    public function getSignatureByClassName($className, $methodName)
    {
        return $this->getMethodSignature($className, $methodName);
    }

    /**
     * Extract the method signature directly from a ReflectionMethod, avoiding a redundant getMethod() call
     *
     * @param ReflectionMethod $method
     * @return array{'name': string, 'type'?: string, 'default'?: mixed, 'variadic'?: bool}[]
     */
    public function getSignatureByReflectionMethod(ReflectionMethod $method): array
    {
        return $this->buildSignatureFromParameters($method->getParameters());
    }

    /**
     * Returns an array of parameters used by the given method. Fields for each parameter include:
     *  - 'name' - Name of the parameter
     *  - 'type' - Fully Qualified Class Name of the parameter if it is an object
     *  - 'default' - If the parameter provides a default value, the default value.
     * @param string $className Fully qualified class name
     * @param string $methodName Name of the method we're inspecting
     * @param \ReflectionClass<object>|null $refClass Optional existing ReflectionClass for this class
     * @return array{'name': string, 'type'?: string, 'default'?: mixed, 'variadic'?: bool}[] The signature
     * of this methods parameters as an array.
     * @throws \ReflectionException
     */
    private function getMethodSignature($className, $methodName, ?\ReflectionClass $refClass = null)
    {
        if (!$refClass) {
            $refClass = new \ReflectionClass($className);
        }

        try {
            $method = $refClass->getMethod($methodName);
            return $this->buildSignatureFromParameters($method->getParameters());
        } catch (\ReflectionException $e) {
            // The requested method doesn't exist on this class. Check if the class provides a magic call method or die.
            if (!$refClass->hasMethod('__call')) {
                throw $e;
            }
        }
        return [];
    }

    /**
     * Build a signature array from a list of ReflectionParameters
     * This is the shared implementation used by both getMethodSignature and getSignatureByReflectionMethod
     *
     * @param \ReflectionParameter[] $reflectionParameters
     * @return array{'name': string, 'type'?: string, 'default'?: mixed, 'variadic'?: bool}[]
     */
    private function buildSignatureFromParameters(array $reflectionParameters): array
    {
        $methodSignature = [];
        foreach ($reflectionParameters as $parameter) {
            $parameterSignature = [
                'name' => $parameter->getName()
            ];
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $parameterSignature['type'] = $type->getName();
            }
            if ($parameter->isDefaultValueAvailable()) {
                $parameterSignature['default'] = $parameter->getDefaultValue();
            }
            if ($parameter->isVariadic()) {
                $parameterSignature['variadic'] = true;
            }
            $methodSignature[] = $parameterSignature;
        }
        return $methodSignature;
    }
}
