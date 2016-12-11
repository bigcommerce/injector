<?php
declare(strict_types = 1);
namespace Tests\ServiceProvider;

/**
 * Dummy object to test proxying.
 */
class LazyDummy
{
    private $name;

    /**
     * LazyDummy constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName(){
        return $this->name;
    }
}
