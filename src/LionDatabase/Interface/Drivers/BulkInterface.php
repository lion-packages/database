<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Defines a method for inserting multiple rows in one insert
 *
 * @package Lion\Database\Interface\Drivers
 */
interface BulkInterface
{
    /**
     * Nesting multiple values in an insert run
     *
     * @param array<int, string> $columns [List of columns]
     * @param array<int, array<int|string, mixed>> $rows [Insertion rows]
     *
     * @return self
     */
    public static function bulk(array $columns, array $rows): self;
}
