<?php

declare(strict_types=1);

namespace Bigcommerce\Injector\Cache;

interface MultiGetCacheInterface
{
    /**
     * Retrieve all entries of the cache.
     *
     * @return array<string, mixed>
     */
    public function getAll(): array;
}
