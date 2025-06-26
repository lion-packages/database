<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

use Closure;

/**
 * Defines a method that adds the where condition to the current SQL statement
 *
 * @package Lion\Database\Interface\Drivers
 */
interface WhereInterface
{
    /**
     * Nests the WHERE statement in the current query
     *
     * * If the parameter is boolean, add the Where statement to the current
     * query
     * * If parameter is a function, adds a query group to the current statement
     * * If the parameter is a string, add the where statement and the column to
     * the current query
     *
     * @param bool|Closure|string $where [You can add a WHERE to the current
     * statement, group by group, or return the WHERE statement]
     *
     * @return self
     */
    public static function where(bool|Closure|string $where = true): self;
}
