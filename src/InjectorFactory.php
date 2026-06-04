<?php

declare(strict_types=1);

namespace Bigcommerce\Injector;

use Bigcommerce\Injector\Cache\ArrayServiceCache;
use Bigcommerce\Injector\Cache\ServiceCacheInterface;
use Bigcommerce\Injector\Reflection\CachingClassInspector;
use Bigcommerce\Injector\Reflection\ClassInspector;
use Bigcommerce\Injector\Reflection\ClassInspectorStats;
use Bigcommerce\Injector\Reflection\ParameterInspector;
use Bigcommerce\Injector\Reflection\ReflectionClassCache;
use Psr\Container\ContainerInterface;

class InjectorFactory
{
    /**
     * @param array<string, array{0: array|false|null}> $precomputedConstructorSignatures
     */
    public static function create(
        ContainerInterface $container,
        int $reflectionClassCacheSize = 50,
        ?ServiceCacheInterface $serviceCache = null,
        array $precomputedConstructorSignatures = [],
    ): InjectorInterface {
        $classInspector = new ClassInspector(
            new ReflectionClassCache($reflectionClassCacheSize),
            new ParameterInspector(),
            new ClassInspectorStats(),
        );

        $cachingClassInspector = new CachingClassInspector(
            $classInspector,
            $serviceCache ?? new ArrayServiceCache(),
            $precomputedConstructorSignatures,
        );

        return new Injector($container, $cachingClassInspector);
    }
}
