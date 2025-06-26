<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Defines a method to add the 'greater than / >' operator
 *
 * @package Lion\Database\Interface\Drivers
 */
interface GreaterThanInterface
{
    /**
     * Adds a "greater than / >" to the current statement
     *
     * @param string $columnOrValue Column name or value
     * @param mixed $value Value of the condition
     *
     * @return self
     */
    public static function greaterThan(mixed $columnOrValue, mixed $value = null): self;
}
