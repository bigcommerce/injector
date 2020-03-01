<?php

namespace Bigcommerce\Injector;

use Bigcommerce\Injector\ServiceProvider\BindingClosureFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use ProxyManager\Proxy\VirtualProxyInterface;

abstract class InjectorServiceProvider implements ServiceProviderInterface
{
    /**
     * @var InjectorInterface
     */
    protected $injector;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var BindingClosureFactory
     */
    private $closureFactory;

    /**
     * @param InjectorInterface $injector
     * @param Container $container
     * @param BindingClosureFactory $closureFactory
     */
    public function __construct(
        InjectorInterface $injector,
        Container $container,
        BindingClosureFactory $closureFactory
    ) {
        $this->injector = $injector;
        $this->container = $container;
        $this->closureFactory = $closureFactory;
    }

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $app
     * @return void
     */
    abstract public function register(Container $app);

    /**
     * Shortcut alias for Container::offsetGet()
     *
     * @param string $id
     * @return mixed
     * @throws \InvalidArgumentException if the identifier is not defined
     * @see Container::offsetGet()
     */
    protected function get($id)
    {
        return $this->container[$id];
    }

    /**
     * Retrieve a lazy proxy of a service definition. Proxies will mirror the interface of the service requested,
     * but wont instantiate that service (and it's dependencies) until a method on that interface is called.
     * @param string $className FQCN of the class being proxied
     * @param string $id Container service ID. Optional if the service name matches the class name.
     * @return object|VirtualProxyInterface
     */
    protected function getLazy(string $className, string $id = '')
    {
        if (!$id) {
            $id = $className;
        }
        return $this->closureFactory->createServiceProxy($this->container, $id, $className);
    }

    /**
     * Alias for Injector::create()
     * @param string $className
     * @param array $parameters
     * @return object
     * @throws \Exception
     * @see InjectorInterface::create()
     */
    protected function create($className, $parameters = [])
    {
        return $this->injector->create($className, $parameters);
    }

    /**
     * Shortcut for binding a service to the container. Every call to ::get for this service will receive the same
     * instance of the service.
     *
     * @param string $id
     * @param callable|mixed $value
     * @return void
     * @throws \Exception
     * @see Container::offsetSet()
     */
    protected function bind($id, $value)
    {
        $this->container[$id] = $value;
    }

    /**
     * Shortcut for binding a factory callable to the container. Every call to ::get for this service will receive a
     * new instance of this service
     *
     * @param string $id
     * @param callable $value
     * @return void
     * @throws \Exception
     * @see Container::factory()
     */
    protected function bindFactory($id, callable $value)
    {
        $this->bind($id, $this->container->factory($value));
    }

    /**
     * Alias a service name to another one within the service container, allowing for example
     * concrete types to be aliased to an interface, or legacy string service keys against concrete class names.
     *
     * @param string $aliasKey
     * @param string $serviceKey
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function alias($aliasKey, $serviceKey)
    {
        // Bound as a factory to ALWAYS pass through to underlying definition.
        $this->bindFactory(
            $aliasKey,
            function (Container $app) use ($serviceKey) {
                return $app[$serviceKey];
            }
        );
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
     * @return void
     */
    protected function lazyBind($className, callable $parameterFactory = null)
    {
        $this->bind(
            $className,
            $this->closureFactory->createAutoWireProxyClosure($className, $parameterFactory)
        );
    }

    /**
     * Automatically bind and wire a lazy factory using the Injector. Accepts an optional callable to build parameter
     * overrides. Lazy services will return Proxies when retrieved, which will only fetch the underlying real service
     * when first used. See http://ocramius.github.io/presentations/proxy-pattern-in-php/
     *
     * HINT: You can use this binding type for expensive services that you *might* need but don't want to instantiate
     * eagerly.
     *
     * @param string $className FQCN of a class to auto-wire bind.
     * @param callable|null $parameterFactory Callable to generate parameters to inject to the service. Will receive
     * the IoC container as its first parameter.
     * @return void
     */
    protected function lazyBindFactory($className, callable $parameterFactory = null)
    {
        $this->bindFactory(
            $className,
            $this->closureFactory->createAutoWireProxyClosure($className, $parameterFactory)
        );
    }

    /**
     * Automatically bind and wire a service using the Injector. Accepts an optional callable to build parameter
     * overrides
     *
     * @param string $className FQCN of a class to auto-wire bind.
     * @param callable|null $parameterFactory Callable to generate parameters to inject to the service. Will receive
     * the IoC container as its first parameter.
     * @return void
     * @throws \Exception
     */
    protected function autoBind($className, callable $parameterFactory = null)
    {
        $this->bind(
            $className,
            $this->closureFactory->createAutoWireClosure($className, $parameterFactory)
        );
    }

    /**
     * Automatically bind and wire a factory using the Injector. Accepts an optional callable to build parameter
     * overrides.
     *
     * @param string $className FQCN of a class to auto-wire bind.
     * @param callable|null $parameterFactory Callable to generate parameters to inject to the service. Will receive
     * the IoC container as its first parameter.
     * @return void
     * @throws \Exception
     */
    protected function autoBindFactory($className, callable $parameterFactory = null)
    {
        $this->bindFactory(
            $className,
            $this->closureFactory->createAutoWireClosure($className, $parameterFactory)
        );
    }
}
