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
     * @param string $column [Column name]
     * @param mixed $lessThanOrEqualTo [Less than or equal to]
     *
     * @return static
     */
    public static function lessThanOrEqualTo(string $column, mixed $lessThanOrEqualTo): static;
}
