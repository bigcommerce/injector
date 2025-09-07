<?php

declare(strict_types=1);

namespace Bigcommerce\Injector\Reflection;

use Countable;
use ReflectionClass;

class ReflectionClassCache implements Countable
{
    private int $maxSize;

    /**
     * @var ReflectionClass<object>[]
     */
    private array $map = [];

    public function __construct(int $maxSize)
    {
        $this->maxSize = $maxSize;
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @return void
     */
    public function put(ReflectionClass $reflection): void
    {
        if (count($this->map) >= $this->maxSize) {
            $this->evictOneObject();
        }
        $this->map[$reflection->getName()] = $reflection;
    }

    /**
     * @param string $className
     * @return ReflectionClass<object>|null
     */
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

    public function count(): int
    {
        return count($this->map);
    }

    private function evictOneObject(): void
    {
        // Get the least recently used item (first in insertion order)
        $lruClassName = array_key_first($this->map);
        if ($lruClassName !== null) {
            unset($this->map[$lruClassName]);
        }
    }
}
