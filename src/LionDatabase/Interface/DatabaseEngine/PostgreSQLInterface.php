<?php

declare(strict_types=1);

namespace Lion\Database\Interface\DatabaseEngine;

use Closure;
use Lion\Database\Interface\DatabaseCapsuleInterface;
use PDOException;
use stdClass;

/**
 * Configuration interface for the PostgreSQL database engine
 *
 * @package Lion\Database\Interface\DatabaseEngine
 */
interface PostgreSQLInterface
{
    /**
     * Initializes a PostgreSQL database connection and runs a process
     *
     * @param Closure $callback [Function that is executed]
     *
     * @return stdClass
     *         | array<int|string, stdClass|array<int|string, mixed>|DatabaseCapsuleInterface>
     *         | DatabaseCapsuleInterface
     *
     * @throws PDOException [If the database process fails]
     *
     * @internal
     */
    public static function postgresql(Closure $callback): stdClass|array|DatabaseCapsuleInterface;
}
