<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

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
     * @return self
     */
    public static function update(array $rows): self;
}
