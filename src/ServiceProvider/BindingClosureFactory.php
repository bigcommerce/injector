<?php
declare(strict_types=1);

namespace Bigcommerce\Injector\ServiceProvider;

use Bigcommerce\Injector\InjectorInterface;
use Pimple\Container;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

/**
 * Factory to create closures for ServiceProvider bindings allowing JIT injection and Lazy Bindings through Proxies.
 */
class BindingClosureFactory
{
    /**
     * @var LazyLoadingValueHolderFactory
     */
    private LazyLoadingValueHolderFactory $proxyFactory;

    /**
     * @var InjectorInterface
     */
    private InjectorInterface $injector;

    /**
     * BindingClosureFactory constructor.
     * @param LazyLoadingValueHolderFactory $proxyFactory
     * @param InjectorInterface $injector
     */
    public function __construct(LazyLoadingValueHolderFactory $proxyFactory, InjectorInterface $injector)
    {
        $this->proxyFactory = $proxyFactory;
        $this->injector = $injector;
    }

    /**
     * Generate a closure that will use the Injector to auto-wire a service definition.
     *
     * @param string $className FQCN of a class to auto-wire bind.
     * @param callable|null $parameterFactory Callable to generate parameters to inject to the service. Will receive
     * the IoC container as its first parameter.
     * @return \Closure
     */
    public function createAutoWireClosure($className, ?callable $parameterFactory = null)
    {
        $injector = $this->injector;
        return static function (Container $app) use ($className, $parameterFactory, $injector) {
            $parameters = $parameterFactory ? $parameterFactory($app) : [];
            return $injector->create($className, $parameters);
        };
    }

    /**
     * Automatically bind and wire a lazy service using the Injector. Accepts an optional callable to build parameter
     * overrides. Lazy services will return Proxies when retrieved, which will only fetch the underlying real service
     * when first used. See http://ocramius.github.io/presentations/proxy-pattern-in-php/
     *
     * HINT: You can use this binding type for expensive services that you *might* need but don't want to instantiate
     * eagerly.
     *
     * @param string $className FQCN of a class to auto-wire bind.
     * @param callable|null $parameterFactory Callable to generate parameters to inject to the service. Will receive
     * the IoC container as its first parameter.
     * @return callable
     */
    public function createAutoWireProxyClosure($className, ?callable $parameterFactory = null)
    {
        $proxyFactory = $this->proxyFactory;
        $createAutoWireClosure = [$this, 'createAutoWireClosure'];
        return static function (Container $app) use ($className, $parameterFactory, $proxyFactory, $createAutoWireClosure) {
            $serviceFactory = $createAutoWireClosure($className, $parameterFactory);
            return $proxyFactory->createProxy(
                $className,
                static function (&$wrappedObject, $proxy, $method, $parameters, &$initializer) use ($serviceFactory, $app) {
                    $wrappedObject = $serviceFactory($app);
                    $initializer = null;
                    return true;
                }
            );
        };
    }

    /**
     * Create a proxy for an existing service definition. As opposed to autowired proxy definitions which are defined
     * by the service itself, these proxies are defined by the client allowing clients to request proxied versions of
     * otherwise non-lazy service definitions.
     * This is preferable for services that should otherwise be initialised on construction (for example, those with
     * complex dependency graphs that should normally be covered by service definition tests).
     * @param Container $app
     * @param string $serviceName The name of the service in the container.
     * @param string $serviceClassName The FQCN of the service being proxied
     * @return \ProxyManager\Proxy\VirtualProxyInterface
     */
    public function createServiceProxy(Container $app, string $serviceName, string $serviceClassName)
    {
        $proxyFactory = $this->proxyFactory;
        return $proxyFactory->createProxy(
            $serviceClassName,
            static function (&$wrappedObject, $proxy, $method, $parameters, &$initializer) use ($app, $serviceName, $serviceClassName) {
                $service = $app->offsetGet($serviceName);
                if (! ($service instanceof $serviceClassName)) {
                    $invalidClassName = get_class($service);
                    throw new \RuntimeException(
                        "Invalid proxied/lazy service definition: tried to proxy '$serviceName' as an instance ".
                        "of '$serviceClassName', but actually received an instance of '$invalidClassName' when retrieving ".
                        "'$serviceName' from the container. To fix this, find the '\$this->getLazy($serviceName)' service ".
                        "binding and make sure it specifies the actual class name that will be returned by that service."
                    );
                }
                $wrappedObject = $service;
                $initializer = null;
                return true;
            }
        );
    }
}
