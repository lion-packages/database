<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Defines a method to add the 'less than / <' operator
 *
 * @package Lion\Database\Interface\Drivers
 */
interface LessThanInterface
{
    /**
     * Adds a "less than / <" to the current statement
     *
     * @param string $columnOrValue Column name or value
     * @param mixed $value Value of the condition
     *
     * @return self
     */
    public static function lessThan(mixed $columnOrValue, mixed $value = null): self;
}
