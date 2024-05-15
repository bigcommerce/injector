<?php

declare(strict_types=1);

namespace Bigcommerce\Injector\Cache;

interface MultiGetCacheInterface
{
    /**
     * @return array<string, string>
     */
    public function getAll(): array;
}
