<?php

declare(strict_types=1);

namespace Bigcommerce\Injector\Reflection;

use Countable;
use ReflectionClass;

class ReflectionClassCache implements Countable
{
    private int $maxSize;

    /**
     * Track the number of entries to avoid calling count() on every put()
     */
    private int $size = 0;

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
        // if already present, remove it so we can re-insert it as most recently used item (size unchanged)
        if (isset($this->map[$className])) {
            unset($this->map[$className]);
        } elseif ($this->size >= $this->maxSize) {
            // at capacity, evict one to make room (size unchanged: -1 evict + 1 insert)
            $this->evictOneObject();
        } else {
            // new entry with room to spare
            $this->size++;
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
            // promote to most recently used — but skip if already at the end
            if (array_key_last($this->map) !== $className) {
                $reflection = $this->map[$className];
                unset($this->map[$className]);
                $this->map[$className] = $reflection;
                return $reflection;
            }
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
        return $this->size;
    }

    private function evictOneObject(): void
    {
        $lruClassName = array_key_first($this->map);
        if ($lruClassName !== null) {
            unset($this->map[$lruClassName]);
        }
    }
}
