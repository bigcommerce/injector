<?php
namespace Bigcommerce\Injector\Adapter;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;
use Pimple\Container;

/**
 * Adapt Pimple's container to ContainerInterop Interface
 * @package Bigcommerce\Injector\Adapter
 */
class PimpleContainerAdapter implements ContainerInterface
{
    /**
     * @var Container
     */
    private $pimpleContainer;

    public function __construct(Container $pimpleContainer)
    {
        $this->pimpleContainer = $pimpleContainer;
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
        return $this->pimpleContainer->offsetGet($id);
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
        return $this->pimpleContainer->offsetExists($id);
    }
}