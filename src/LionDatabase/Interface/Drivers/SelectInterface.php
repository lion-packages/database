<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Implements a method to select data in SQL queries.
 */
interface SelectInterface
{
    /**
     * Nests the SELECT statement in the current query.
     *
     * @return self
     */
    public static function select(): self;
}
