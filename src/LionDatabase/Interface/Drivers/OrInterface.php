<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

use Closure;

/**
 * Defines a method that adds the or condition to the current SQL statement
 *
 * @package Lion\Database\Interface\Drivers
 */
interface OrInterface
{
    /**
     * Nests the OR statement in the current query
     *
     * * If the parameter is boolean, add the Where statement to the current
     * query
     * * If parameter is a function, adds a query group to the current statement
     * * If the parameter is a string, add the where statement and the column to
     * the current query
     *
     * @param bool|Closure|string $or [You can add a OR to the current
     * statement, group by group, or return the OR statement]
     *
     * @return static
     */
    public static function or(bool|Closure|string $or = true): static;
}
