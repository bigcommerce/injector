<?php
namespace Bigcommerce\Injector;

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
interface InjectorInterface
{
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
     * @throws \Exception
     * @return object
     */
    public function create($className, $parameters = []);

    /**
     * Call a method with parameter dependency injection from the IoC container. This is functionally equivalent to
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
     * @throws \Exception
     */
    public function invoke($instance, $methodName, $parameters = []);
}
