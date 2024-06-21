<?php

declare(strict_types=1);

namespace Bigcommerce\Injector\Reflection;

class ClassInspectorStats
{
    private int $reflectionClassesCreated = 0;

    public function incrementReflectionClassesCreated(): void
    {
        $this->reflectionClassesCreated++;
    }

    public function getReflectionClassesCreated(): int
    {
        return $this->reflectionClassesCreated;
    }

    public function reset(): void
    {
        $this->reflectionClassesCreated = 0;
    }
}
