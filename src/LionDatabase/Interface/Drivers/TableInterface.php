<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Declare a method to define a table
 *
 * @package Lion\Database\Interface\Drivers
 */
interface TableInterface
{
    /**
     * Nests the TABLE statement in the current query
     *
     * @param string|bool $table [Nests the table in the current query or nests
     * the TABLE statement in the current query]
     * @param bool $withDatabase [Determines whether to nest the current
     * database in the table]
     *
     * @return self
     */
    public static function table(string|bool $table = true, bool $withDatabase = false): self;
}
