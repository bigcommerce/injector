<?php

declare(strict_types=1);

namespace Bigcommerce\Injector;

use Bigcommerce\Injector\Cache\ArrayServiceCache;
use Bigcommerce\Injector\Reflection\ClassInspector;
use Bigcommerce\Injector\Reflection\ClassInspectorStats;
use Bigcommerce\Injector\Reflection\ParameterInspector;
use Bigcommerce\Injector\Reflection\ReflectionClassMap;
use Psr\Container\ContainerInterface;

class InjectorFactory
{
    public static function create(ContainerInterface $container): InjectorInterface
    {
        $classInspector = new ClassInspector(
            new ReflectionClassMap(50),
            new ParameterInspector(),
            new ArrayServiceCache(),
            new ClassInspectorStats()
        );

        return new Injector($container, $classInspector);
    }
}
