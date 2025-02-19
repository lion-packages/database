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
     * @param string $column [Column name]
     * @param mixed $notEqualTo [Not equal to]
     *
     * @return static
     */
    public static function notEqualTo(string $column, mixed $notEqualTo): static;
}
