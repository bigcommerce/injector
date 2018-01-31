<?php
namespace Tests\Dummy;

use Bigcommerce\Injector\Cache\ServiceCacheInterface;

class DummySimpleConstructor
{
    /**
     * @var ServiceCacheInterface
     */
    private $cache;

    /**
     * @var DummyDependency
     */
    private $dummyDependency;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $age;

    /**
     * @var string[]
     */
    private $args;

    /**
     * DummySimpleConstructor constructor.
     * @param ServiceCacheInterface $cache
     * @param DummyDependency $dummyDependency
     * @param string $name
     * @param int $age
     * @param string ...$args
     */
    public function __construct(ServiceCacheInterface $cache, DummyDependency $dummyDependency, $name, $age = 25, string ...$args)
    {

        $this->cache = $cache;
        $this->dummyDependency = $dummyDependency;
        $this->name = $name;
        $this->age = $age;
        foreach ($args as $arg) {
            $this->args[] = $arg;
        }
    }

    /**
     * @return ServiceCacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return DummyDependency
     */
    public function getDummyDependency()
    {
        return $this->dummyDependency;
    }

    /**
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getArgs()
    {
        return $this->args;
    }
}
