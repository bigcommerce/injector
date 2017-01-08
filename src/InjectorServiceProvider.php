<?php
namespace Bigcommerce\Injector;

use Bigcommerce\Injector\ServiceProvider\BindingClosureFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

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
     * @see Container::offsetGet()
     * @param string $id
     * @return mixed
     * @throws \InvalidArgumentException if the identifier is not defined
     */
    protected function get($id)
    {
        return $this->container[$id];
    }

    /**
     * Alias for Injector::create()
     * @see InjectorInterface::create()
     * @param string $className
     * @param array $parameters
     * @return object
     * @throws \Exception
     */
    protected function create($className, $parameters = [])
    {
        return $this->injector->create($className, $parameters);
    }

    /**
     * Shortcut for binding a service to the container. Every call to ::get for this service will receive the same
     * instance of the service.
     *
     * @see Container::offsetSet()
     * @param string $id
     * @param callable|mixed $value
     * @throws \Exception
     * @return void
     */
    protected function bind($id, $value)
    {
        $this->container[$id] = $value;
    }

    /**
     * Shortcut for binding a factory callable to the container. Every call to ::get for this service will receive a
     * new instance of this service
     *
     * @see Container::factory()
     * @param string $id
     * @param callable $value
     * @throws \Exception
     * @return void
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
     * @throws \InvalidArgumentException
     * @return void
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
     * @throws \Exception
     * @return void
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
     * @throws \Exception
     * @return void
     */
    protected function autoBindFactory($className, callable $parameterFactory = null)
    {
        $this->bindFactory(
            $className,
            $this->closureFactory->createAutoWireClosure($className, $parameterFactory)
        );
    }
}
