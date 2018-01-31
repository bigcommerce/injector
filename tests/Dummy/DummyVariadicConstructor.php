<?php
namespace Tests\Dummy;

class DummyVariadicConstructor
{
    /**
     * @var DummyString[]
     */
    private $args;

    /**
     * @param DummyString ...$args
     */
    public function __construct(DummyString ...$args)
    {
        foreach ($args as $arg) {
            $this->args[] = $arg;
        }
    }

    /**
     * @return DummyString[]
     */
    public function getArgs()
    {
        return $this->args;
    }
}
