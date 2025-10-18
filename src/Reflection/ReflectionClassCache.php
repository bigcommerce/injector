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
        $className = $reflection->getName();
        // if already present, remove it so we can re-insert it as most recently used item
        if (isset($this->map[$className])) {
            unset($this->map[$className]);
        } elseif (count($this->map) >= $this->maxSize) {
            $this->evictOneObject();
        }
        $this->map[$className] = $reflection;
    }

    /**
     * @param string $className
     * @return ReflectionClass<object>|null
     */
    public function get(string $className): ?ReflectionClass
    {
        if (isset($this->map[$className])) {
            // promote to most recently used item by re-inserting this to the end of the array
            $reflection = $this->map[$className];
            unset($this->map[$className]);
            $this->map[$className] = $reflection;
            return $reflection;
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
        $lruClassName = array_key_first($this->map);
        if ($lruClassName !== null) {
            unset($this->map[$lruClassName]);
        }
    }
}
