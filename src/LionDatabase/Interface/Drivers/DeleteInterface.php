<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Declare a method that deletes data in SQL
 *
 * @package Lion\Database\Interface\Drivers
 */
interface DeleteInterface
{
    /**
     * Nests the DELETE statement in the current query
     *
     * @return static
     */
    public static function delete(): static;
}
