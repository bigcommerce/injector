<?php
namespace Tests\Dummy;

/**
 * Dummy class with magic __call method for ParameterInspector test
 */
class MagicCallDummy
{
    public function __call($name, $arguments)
    {
        //Do nothing
    }
}
