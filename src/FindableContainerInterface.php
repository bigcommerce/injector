<?php

namespace Bigcommerce\Injector;

use Psr\Container\ContainerInterface;

/**
 * A container that can return an entry (or NULL when absent) in a single lookup, letting callers avoid the ->has() +
 * ->get() double lookup for the common case.
 *
 * find() alone cannot distinguish "no entry for this id" from "entry registered, but its value is NULL" - both
 * return NULL. Callers that need to tell those apart (see Injector) must fall back to ->has() specifically when
 * find() returns NULL; that fallback only costs a second lookup for the rare null-valued case, not for every call.
 */
interface FindableContainerInterface extends ContainerInterface
{
    /**
     * Return the entry for the given id, or NULL if the container has no entry for it OR the entry's value is
     * itself NULL. Use ->has($id) to tell those two cases apart when it matters.
     *
     * @param string $id
     * @return mixed
     */
    public function find(string $id): mixed;
}
