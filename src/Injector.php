<?php
namespace Bigcommerce\Injector;

use Bigcommerce\Injector\Exception\InjectorInvocationException;
use Bigcommerce\Injector\Exception\MissingRequiredParameterException;
use Bigcommerce\Injector\Reflection\ClassInspectorInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionException;

/**
 * The Injector provides instantiation of objects (or invocation of methods) within the BC application and
 * automatically injects dependencies from the IoC container. It behaves as a factory for any class wiring dependencies
 * JIT which serves two primary purposes:
 *  - Binding of service definitions within the IoC container - allowing constructor signatures to define their
 *      dependencies and in most cases reducing the touch-points required for refactors.
 *  - Construction of objects with dependencies served by the IoC container during the post-bootstrap application
 *      lifecycle (such as factories building command objects dynamically) without passing around the IoC container
 *      to avoid Service Location/Implicit Dependencies
 *
 * NOTE: The second use case should ONLY apply when objects that depend on services need to be constructed dynamically.
 * You should generally strive to construct your entire dependency object graph at construction rather than dynamically
 * to ensure dependencies are clear.
 *
 * Return type hinting is provided for all constructed objects in IntelliJ/PHPStorm via the dynamicReturnTypes
 * extension. Make sure you install it if you are using the injector to provide IDE hinting.
 * @package \Bigcommerce\Injector
 */
class Injector implements InjectorInterface
{
    /**
     * Regular Expressions matching dependencies that can be automatically created using their class name, even if they
     * are not defined in the IoC Container.
     *
     * @var string[]
     */
    protected array $autoCreateWhiteList = [];

    /**
     * Cached results of canAutoCreate calls
     *
     * @var array<string, bool>
     */
    private array $autoCreateCache = [];

    /** @var array<string, bool> cached container->has() results per type */
    private array $containerHasCache = [];

    public function __construct(private readonly ContainerInterface $container, private readonly ClassInspectorInterface $classInspector)
    {
    }

    /**
     * Instantiate an object and attempt to inject the dependencies for the class by mapping constructor parameter \
     * names to objects registered within the IoC container.
     *
     * The optional $parameters passed to this method accept and will inject values based on:
     *  - Type:  [Cache::class => new RedisCache()] will inject RedisCache to each parameter typed Cache::class
     *  - Name:  ["cache" => new RedisCache()] will inject RedisCache to the parameter named $cache
     *  - Index: [ 3 => new RedisCache()] will inject RedisCache to the 4th parameter (zero index)
     *
     * @param string $className The fully qualified class name for the object we're creating
     * @param array $parameters An optional array of additional parameters to pass to the created objects constructor.
     * @return object
     * @throws InjectorInvocationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function create($className, $parameters = [])
    {
        $signature = $this->classInspector->getCallableConstructorSignature($className);

        if ($signature === null) {
            return new $className();
        }

        if ($signature === false) {
            throw new InjectorInvocationException(
                "Injector failed to create $className - constructor isn't public." .
                " Do you need to use a static factory method instead?"
            );
        }

        try {
            $parameters = $this->buildParameterArray($signature, $parameters);

            return new $className(...$parameters);
        } catch (MissingRequiredParameterException $e) {
            throw new InjectorInvocationException(
                "Can't create $className " .
                " - __construct() missing parameter '" . $e->getParameterString() . "'" .
                " could not be found. Either register it as a service or pass it to create via parameters."
            );
        } catch (InjectorInvocationException $e) {
            //Wrap the exception stack for recursive calls to aid debugging
            throw new InjectorInvocationException(
                $e->getMessage() .
                PHP_EOL . " => (Called when creating $className)"
            );
        }
    }

    /**
     * Call a method with auto dependency injection from the IoC container. This is functionally equivalent to
     * call_user_func_array with auto-wiring against the service container.
     * Note: Whilst this method is useful for dynamic dispatch i.e controller actions, generally you should be
     * calling methods concretely. Use this wisely and ensure you always document return types.
     *
     * The optional $parameters passed to this method accept and will inject values based on:
     *  - Type:  [Cache::class => new RedisCache()] will inject RedisCache to each parameter typed Cache::class
     *  - Name:  ["cache" => new RedisCache()] will inject RedisCache to the parameter named $cache
     *  - Index: [ 3 => new RedisCache()] will inject RedisCache to the 4th parameter (zero index)
     *
     * @param object $instance
     * @param string $methodName
     * @param array $parameters
     * @return mixed
     * @throws InjectorInvocationException
     * @throws InvalidArgumentException
     */
    public function invoke($instance, $methodName, $parameters = [])
    {
        if (!is_object($instance)) {
            throw new InvalidArgumentException(
                "Attempted Injector::invoke on a non-object: " . gettype($instance) . "."
            );
        }
        $className = get_class($instance);
        try {
            $parameters = $this->buildParameterArray(
                $this->classInspector->getMethodSignature($className, $methodName),
                $parameters
            );

            return $instance->{$methodName}(...$parameters);
        } catch (MissingRequiredParameterException $e) {
            throw new InjectorInvocationException(
                "Can't invoke method $className::$methodName()" .
                " - missing parameter '" . $e->getParameterString() . "'" .
                " could not be found. Either register it as a service or pass it to invoke via parameters."
            );
        } catch (ReflectionException $e) {
            throw new InjectorInvocationException(
                "Failed to invoke $className::$methodName - method doesn't exist."
            );
        }
    }

    /**
     * @param string $regex
     * @return void
     */
    public function addAutoCreate($regex)
    {
        $this->autoCreateWhiteList[] = "/^" . $regex . "$/ims";
        $this->autoCreateCache = [];
    }

    /**
     * @return \string[]
     */
    public function getAutoCreateWhiteList()
    {
        return $this->autoCreateWhiteList;
    }

    /**
     * @param string $className
     * @return bool
     */
    public function canAutoCreate($className): bool
    {
        $cached = $this->autoCreateCache[$className] ?? null;
        if ($cached !== null) {
            return $cached;
        }

        foreach ($this->autoCreateWhiteList as $regex) {
            if (preg_match($regex, $className)) {
                $this->autoCreateCache[$className] = true;
                return true;
            }
        }

        $this->autoCreateCache[$className] = false;
        return false;
    }

    private function containerHas(string $type): bool
    {
        return $this->containerHasCache[$type] ??= $this->container->has($type);
    }

    /**
     * @param array $methodSignature
     * @param array $providedParameters
     * @return array
     * @throws InjectorInvocationException
     * @throws MissingRequiredParameterException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function buildParameterArray($methodSignature, $providedParameters)
    {
        if (empty($providedParameters)) {
            return $this->buildParameterArrayFromContainer($methodSignature);
        }

        $parameters = [];
        foreach ($methodSignature as $position => $parameterData) {
            if (!isset($parameterData['variadic'])) {
                $parameters[$position] = $this->resolveParameter($position, $parameterData, $providedParameters);
            } else {
                foreach ($providedParameters as $variadicParameter) {
                    $parameters[] = $variadicParameter;
                }
            }
        }
        return $parameters;
    }

    /**
     * Resolve all parameters purely from container / auto-create / defaults (no provided params).
     *
     * @param array $methodSignature
     * @return array
     * @throws MissingRequiredParameterException
     * @throws InjectorInvocationException
     * @throws ReflectionException
     */
    private function buildParameterArrayFromContainer($methodSignature)
    {
        $parameters = [];
        foreach ($methodSignature as $parameterData) {
            if (isset($parameterData['variadic'])) {
                break;
            }
            $type = $parameterData['type'] ?? false;
            if ($type) {
                if ($this->containerHas($type)) {
                    $parameters[] = $this->container->get($type);
                    continue;
                }
                if ($this->canAutoCreate($type)) {
                    $parameters[] = $this->create($type);
                    continue;
                }
            }
            if (array_key_exists('default', $parameterData)) {
                $parameters[] = $parameterData['default'];
                continue;
            }
            $name = $parameterData['name'];
            throw new MissingRequiredParameterException(
                $name,
                $type,
                sprintf('Could not find required parameter "%s" for method', $name)
            );
        }
        return $parameters;
    }

    /**
     * Resolve a single parameter by searching: provided params (by name, index, type),
     * then the container, then auto-create, then defaults.
     *
     * @param int $position
     * @param array $parameterData
     * @param array $providedParameters
     * @throws MissingRequiredParameterException
     * @return mixed
     */
    private function resolveParameter($position, $parameterData, &$providedParameters)
    {
        $name = $parameterData['name'];
        $type = $parameterData['type'] ?? false;
        if (array_key_exists($name, $providedParameters)) {
            $result = $providedParameters[$name];
            unset($providedParameters[$name]);
            return $result;
        }
        if (array_key_exists($position, $providedParameters)) {
            $result = $providedParameters[$position];
            unset($providedParameters[$position]);
            return $result;
        }
        if ($type) {
            if (array_key_exists($type, $providedParameters)) {
                $result = $providedParameters[$type];
                unset($providedParameters[$type]);
                return $result;
            }
            if ($this->containerHas($type)) {
                return $this->container->get($type);
            }
            if ($this->canAutoCreate($type)) {
                return $this->create($type);
            }
        }
        if (array_key_exists("default", $parameterData)) {
            return $parameterData['default'];
        }
        throw new MissingRequiredParameterException(
            $name,
            $type,
            sprintf('Could not find required parameter "%s" for method', $name)
        );
    }
}
