<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

use Lion\Database\Drivers\MySQL;

/**
 * Declare a method that updates data in SQL
 *
 * @package Lion\Database\Interface\Drivers
 */
interface UpdateInterface
{
    /**
     * Nests the UPDATE statement in the current query
     *
     * @param array<string, mixed> $rows [List of values]
     *
     * @return static
     */
    public static function update(array $rows): static;
}
