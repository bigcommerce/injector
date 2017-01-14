<?php
namespace Bigcommerce\Injector\Adapter;

use Bigcommerce\Injector\Adapter\Exception\ServiceNotFoundException;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;
use Pimple\Container;

/**
 * Adapt a simple array container (i.e Pimple) to ContainerInterop Interface
 * @package Bigcommerce\Injector\Adapter
 */
class ArrayContainerAdapter implements ContainerInterface
{
    /**
     * @var array
     */
    private $arrayContainer;

    public function __construct(array $pimpleContainer)
    {
        $this->arrayContainer = $pimpleContainer;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if(!$this->has($id)){
            throw new ServiceNotFoundException("Service not found in container ($id).");
        }
        return $this->arrayContainer[$id];
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        return isset($this->arrayContainer[$id]);
    }
}