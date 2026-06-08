<?php
namespace Bigcommerce\Injector\Adapter;

use Bigcommerce\Injector\Adapter\Exception\ServiceNotFoundException;
use Bigcommerce\Injector\FindableContainerInterface;
use Bigcommerce\Injector\FindResult;
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
     * Return the entry for the given id, or the FindResult::NotFound sentinel if it isn't in the container. A single
     * lookup, so callers can skip the separate ->has() call. Presence is decided with isset() to match ->has()/->get();
     * when present the entry is returned as-is, including NULL (e.g. a Pimple service whose factory resolves to NULL),
     * which the sentinel keeps distinct from absence.
     *
     * @param string $id
     * @return mixed The entry (which may be NULL), or FindResult::NotFound when absent
     */
    public function find(string $id): mixed
    {
        return isset($this->arrayContainer[$id]) ? $this->arrayContainer[$id] : FindResult::NotFound;
    }
}
