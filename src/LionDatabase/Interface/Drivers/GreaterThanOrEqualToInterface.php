<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Defines a method to add the 'greater than or equal to / >=' operator
 *
 * @package Lion\Database\Interface\Drivers
 */
interface GreaterThanOrEqualToInterface
{
    /**
     * Adds a "greater than or equal to / >=" to the current statement
     *
     * @param string $column [Column name]
     * @param mixed $greaterThanOrEqualTo [Greater than or equal to]
     *
     * @return self
     */
    public static function greaterThanOrEqualTo(string $column, mixed $greaterThanOrEqualTo): self;
}
