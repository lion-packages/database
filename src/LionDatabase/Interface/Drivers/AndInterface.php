<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

use Closure;

/**
 * Defines a method that adds the and condition to the current SQL statement
 *
 * @package Lion\Database\Interface\Drivers
 */
interface AndInterface
{
    /**
     * Nests the AND statement in the current query
     *
     * * If the parameter is boolean, add the Where statement to the current query
     * * If parameter is a function, adds a query group to the current statement
     * * If the parameter is a string, add the where statement and the column to the
     * current query
     *
     * @param Closure|string|bool $and You can add a AND to the current statement, group
     * by group, or return the AND statement
     *
     * @return self
     */
    public static function and(bool|Closure|string $and = true): self;
}
