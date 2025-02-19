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
     * @param string $column [Column name]
     * @param mixed $lessThan [Less than]
     *
     * @return static
     */
    public static function lessThan(string $column, mixed $lessThan): static;
}
