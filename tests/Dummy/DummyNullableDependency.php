<?php
namespace Tests\Dummy;

/**
 * Mirrors a real bug pattern: a required constructor dependency whose type is nullable, and whose container
 * binding can legitimately resolve to NULL (as opposed to the binding simply being absent).
 */
class DummyNullableDependency
{
    /**
     * @var DummySubDependency|null
     */
    private $dependency;

    /**
     * @param DummySubDependency|null $dependency
     */
    public function __construct(?DummySubDependency $dependency)
    {
        $this->dependency = $dependency;
    }

    /**
     * @return DummySubDependency|null
     */
    public function getDependency()
    {
        return $this->dependency;
    }
}
