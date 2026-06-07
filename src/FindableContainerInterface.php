<?php

namespace Bigcommerce\Injector;

use Psr\Container\ContainerInterface;

/**
 * A container that can return an entry (or NULL when absent) in a single lookup, letting callers avoid the ->has() +
 * ->get() double lookup.
 *
 * Containers implementing this must not store NULL entries: a NULL return always means "no entry for this id"
 */
interface FindableContainerInterface extends ContainerInterface
{
    /**
     * Return the entry for the given id, or NULL if the container has no entry for it
     *
     * @param string $id
     * @return mixed
     */
    public function find(string $id): mixed;
}
