<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Defines a method to add the 'less than or equal to / <=' operator
 *
 * @package Lion\Database\Interface\Drivers
 */
interface LessThanOrEqualToInterface
{
    /**
     * Adds a "less than or equal to / <=" to the current statement
     *
     * @param string $columnOrValue Column name or value
     * @param mixed $value Value of the condition
     *
     * @return self
     */
    public static function lessThanOrEqualTo(mixed $columnOrValue, mixed $value = null): self;
}
