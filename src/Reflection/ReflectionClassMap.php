<?php

declare(strict_types=1);

namespace Bigcommerce\Injector\Reflection;

use Countable;
use ReflectionClass;

class ReflectionClassMap implements Countable
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
     *
     * @phpstan-param ReflectionClass<object> $reflection
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

    private function evictOneObject(): void
    {
        array_shift($this->map);
    }

    public function count(): int
    {
        return count($this->map);
    }
}
