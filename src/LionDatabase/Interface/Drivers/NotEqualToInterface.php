<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Defines a method to add the 'not equal to / <>' operator
 *
 * @package Lion\Database\Interface\Drivers
 */
interface NotEqualToInterface
{
    /**
     * Adds a "not equal to / <>" to the current statement
     *
     * @param string $columnOrValue Column name or value
     * @param mixed $value Value of the condition
     *
     * @return self
     */
    public static function notEqualTo(mixed $columnOrValue, mixed $value = null): self;
}
