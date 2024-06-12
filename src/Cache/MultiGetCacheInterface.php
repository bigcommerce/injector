<?php

declare(strict_types=1);

namespace Bigcommerce\Injector\Cache;

/**
 * @template T
 */
interface MultiGetCacheInterface
{
    /**
     * Retrieve all entries of the cache.
     *
     * @return array<string, T>
     */
    public function getAll(): array;
}
