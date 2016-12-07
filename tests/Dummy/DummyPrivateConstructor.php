<?php
namespace Tests\Dummy;

class DummyPrivateConstructor
{
    /**
     * @var string
     */
    private $name;

    /**
     * DummyPrivateConstructor constructor.
     * @param string $name
     */
    private function __construct($name)
    {

        $this->name = $name;
    }

    public static function createInstance($name)
    {
        return new self($name);
    }
}
