<?php
namespace Bigcommerce\Injector\Adapter;

use Bigcommerce\Injector\Adapter\Exception\ServiceNotFoundException;
use Bigcommerce\Injector\FindableContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Adapt a simple array container (i.e Pimple) to ContainerInterop Interface
 * @package Bigcommerce\Injector\Adapter
 */
class ArrayContainerAdapter implements FindableContainerInterface
{
    /**
     * @var array|\ArrayAccess
     */
    private $arrayContainer;

    /**
     * ArrayContainerAdapter constructor.
     * @param array|\ArrayAccess $arrayContainer
     */
    public function __construct($arrayContainer)
    {
        $this->arrayContainer = $arrayContainer;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for this identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if (!isset($this->arrayContainer[$id])) {
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
    public function has($id): bool
    {
        return isset($this->arrayContainer[$id]);
    }

    /**
     * Return the entry for the given id in a single lookup, or NULL if it isn't in the container - but a stored
     * NULL value returns NULL too, so this alone cannot distinguish "absent" from "present and null". Use ->has()
     * to disambiguate when that matters.
     *
     * @param string $id
     * @return mixed
     */
    public function find(string $id): mixed
    {
        return $this->arrayContainer[$id] ?? null;
    }
}
