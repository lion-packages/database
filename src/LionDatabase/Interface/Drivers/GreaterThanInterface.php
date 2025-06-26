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
     * @param string $column [Column name]
     * @param mixed $greaterThan [Greather than]
     *
     * @return self
     */
    public static function greaterThan(string $column, mixed $greaterThan): self;
}
