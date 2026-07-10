<?php
namespace Bigcommerce\Injector\Adapter;

use Bigcommerce\Injector\Adapter\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Adapt a simple array container (i.e Pimple) to ContainerInterop Interface
 * @package Bigcommerce\Injector\Adapter
 */
class ArrayContainerAdapter implements ContainerInterface
{
    /**
     * @var array|\ArrayAccess
     */
    private $arrayContainer;

    private bool $useOptimizedLookup;

    /**
     * ArrayContainerAdapter constructor.
     * @param array|\ArrayAccess $arrayContainer
     */
    public function __construct($arrayContainer)
    {
        $this->arrayContainer = $arrayContainer;
        $this->useOptimizedLookup = is_object($arrayContainer) && method_exists($arrayContainer, 'getIfDefined');
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
        if ($this->useOptimizedLookup) {
            try {
                return $this->arrayContainer[$id];
            } catch (\InvalidArgumentException $e) {
                throw new ServiceNotFoundException("Service not found in container ($id).", 0, $e);
            }
        }
        if (!isset($this->arrayContainer[$id])) {
            throw new ServiceNotFoundException("Service not found in container ($id).");
        }
        return $this->arrayContainer[$id];
    }

    /**
     * Check if the entry exists and retrieve it in a single operation.
     * Avoids the double container lookup of separate has() + get() calls.
     * When the underlying container supports getIfDefined (e.g. JITContainer),
     * this reduces provider loading from 3 calls to 1.
     *
     * @param string $id Identifier of the entry to look for.
     * @param mixed $result Will be set to the entry value if found.
     * @return bool True if the entry was found.
     */
    public function getIfDefined(string $id, mixed &$result): bool
    {
        if ($this->useOptimizedLookup) {
            return $this->arrayContainer->getIfDefined($id, $result);
        }
        if (!isset($this->arrayContainer[$id])) {
            return false;
        }
        $result = $this->arrayContainer[$id];
        return true;
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
}
