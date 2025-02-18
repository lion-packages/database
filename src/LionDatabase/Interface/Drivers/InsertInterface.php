<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Implement a method to insert data into SQL
 *
 * @package Lion\Database\Interface\Drivers
 */
interface InsertInterface
{
    /**
     * Nests the INSERT statement in the current query
     *
     * @param array<string, mixed> $rows [List of values]
     *
     * @return static
     */
    public static function insert(array $rows): static;
}
