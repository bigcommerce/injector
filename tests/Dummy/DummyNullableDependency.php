<?php
namespace Tests\Dummy;

/**
 * Dummy class with a nullable, required dependency. Mirrors real classes (e.g. a storefront resource taking an
 * optional setting) whose dependency can legitimately resolve to NULL from the container.
 */
class DummyNullableDependency
{
    /**
     * @var DummySubDependency|null
     */
    private $dependency;

    /**
     * @var string
     */
    private $name;

    /**
     * @param DummySubDependency|null $dependency
     * @param string $name
     */
    public function __construct(?DummySubDependency $dependency, $name = 'default')
    {
        $this->dependency = $dependency;
        $this->name = $name;
    }

    /**
     * @return DummySubDependency|null
     */
    public function getDependency()
    {
        return $this->dependency;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
