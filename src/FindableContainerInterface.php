<?php

namespace Bigcommerce\Injector;

use Psr\Container\ContainerInterface;

/**
 * A container that can return an entry in a single lookup, letting callers avoid the ->has() + ->get()
 * double lookup.
 *
 * find() returns the {@see FindResult::NotFound} sentinel (not NULL) when there is no entry, so an entry
 * that is itself NULL can be returned and told apart from absence.
 */
interface FindableContainerInterface extends ContainerInterface
{
    /**
     * Return the entry for the given id, or the {@see FindResult::NotFound} sentinel if the container has
     * no entry for it. The entry itself may be NULL, which is distinct from absence.
     *
     * @param string $id
     * @return mixed The entry (which may be NULL), or FindResult::NotFound when absent
     */
    public function find(string $id): mixed;
}
