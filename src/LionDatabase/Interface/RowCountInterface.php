<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

/**
 * Defines the implementation of the PDO rowCount function
 *
 * @package Lion\Database\Interface
 */
interface RowCountInterface
{
    /**
     * Returns the number of rows affected by the last SQL statement
     *
     * @return static
     */
    public function rowCount(): static;
}
