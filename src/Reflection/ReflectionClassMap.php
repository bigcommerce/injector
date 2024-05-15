<?php

declare(strict_types=1);

namespace Bigcommerce\Injector\Reflection;

use Countable;
use ReflectionClass;

class ReflectionClassMap implements Countable
{
    /**
     * @var int
     */
    private $maxSize;

    /**
     * @var ReflectionClass[]
     */
    private $map = [];

    public function __construct(int $maxSize)
    {
        $this->maxSize = $maxSize;
    }

    public function put(ReflectionClass $reflection)
    {
        if (count($this->map) >= $this->maxSize) {
            $this->evictOneObject();
        }
        $this->map[$reflection->getName()] = $reflection;
    }

    public function get(string $className): ?ReflectionClass
    {
        if (isset($this->map[$className])) {
            return $this->map[$className];
        }
        return null;
    }

    public function has(string $className): bool
    {
        return isset($this->map[$className]);
    }

    private function evictOneObject()
    {
        array_shift($this->map);
    }

    public function count()
    {
        return count($this->map);
    }
}
