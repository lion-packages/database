<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

/**
 * Generic function to execute a SQL statement
 *
 * @package Lion\Database\Interface
 */
interface QueryInterface
{
    /**
     * The defined sentence alludes to the current sentence
     *
     * @param string $sql [Defined sentence]
     *
     * @return QueryInterface
     */
    public static function query(string $sql): QueryInterface;
}
