<?php

namespace Tests\Dummy;

use Bigcommerce\Injector\Cache\ServiceCacheInterface;

class DummyNullableParameterConstructor
{

    private ?ServiceCacheInterface $cache;
    private DummyDependency $dummyDependency;

    public function __construct(?ServiceCacheInterface $cache, DummyDependency $dummyDependency)
    {

        $this->cache = $cache;
        $this->dummyDependency = $dummyDependency;
    }

    public function getCache(): ?ServiceCacheInterface
    {
        return $this->cache;
    }

    public function getDummyDependency(): DummyDependency
    {
        return $this->dummyDependency;
    }

}
