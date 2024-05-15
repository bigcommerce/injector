<?php
namespace Tests\Dummy;

class DummySimpleConstructor
{
    /**
     * @var DummyNoConstructor
     */
    private $dummyNoConstructor;

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
     * @param DummyNoConstructor $dummyNoConstructor
     * @param DummyDependency $dummyDependency
     * @param string $name
     * @param int $age
     * @param string ...$args
     */
    public function __construct(DummyNoConstructor $dummyNoConstructor, DummyDependency $dummyDependency, $name, $age = 25, string ...$args)
    {
        $this->dummyNoConstructor = $dummyNoConstructor;
        $this->dummyDependency = $dummyDependency;
        $this->name = $name;
        $this->age = $age;
        foreach ($args as $arg) {
            $this->args[] = $arg;
        }
    }

    /**
     * @return DummyNoConstructor
     */
    public function getDummyNoConstructor()
    {
        return $this->dummyNoConstructor;
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
