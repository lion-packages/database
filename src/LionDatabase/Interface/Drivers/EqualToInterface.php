<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Defines a method to add the 'equal to' operator
 *
 * @package Lion\Database\Interface\Drivers
 */
interface EqualToInterface
{
    /**
     * Adds an "equals to / >" to the current statement
     *
     * @param string $column [Column name]
     * @param mixed $equalTo [Equal to]
     *
     * @return static
     */
    public static function equalTo(string $column, mixed $equalTo): static;
}
