<?php
namespace Bigcommerce\Injector;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

abstract class ServiceProvider implements ServiceProviderInterface
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
     * @param InjectorInterface $injector
     * @param Container $container
     */
    public function __construct(InjectorInterface $injector, Container $container)
    {
        $this->injector = $injector;
        $this->container = $container;
    }

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $app An Container instance
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
        //Bound as a factory to ALWAYS pass through to underlying definition.
        $this->container[$aliasKey] = $this->container->factory(
            function (Container $app) use ($serviceKey) {
                return $app[$serviceKey];
            }
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
            $this->createAutoWireClosure($className, $parameterFactory)
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
            $this->createAutoWireClosure($className, $parameterFactory)
        );
    }

    /**
     * Generate a closure that will use the Injector to auto-wire a service definition.
     *
     * @param string $className FQCN of a class to auto-wire bind.
     * @param callable|null $parameterFactory Callable to generate parameters to inject to the service. Will receive
     * the IoC container as its first parameter.
     * @return \Closure
     */
    private function createAutoWireClosure($className, callable $parameterFactory = null)
    {
        return function (Container $app) use ($className, $parameterFactory) {
            $parameters = $parameterFactory ? $parameterFactory($app) : [];
            return $this->injector->create($className, $parameters);
        };
    }
}
