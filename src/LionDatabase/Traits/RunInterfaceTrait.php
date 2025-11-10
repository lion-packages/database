<?php

declare(strict_types=1);

namespace Lion\Database\Traits;

use InvalidArgumentException;
use Lion\Database\Connection;

/**
 * Declare the run method of the interface.
 */
trait RunInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function run(array $connections): self
    {
        if (empty($connections[Connection::CONNECTION_DEFAULT])) {
            throw new InvalidArgumentException('No default database defined.', 500);
        }

        if (empty($connections[Connection::CONNECTION_CONNECTIONS])) {
            throw new InvalidArgumentException('No databases have been defined.', 500);
        }

        self::$connections = $connections;

        self::$activeConnection = self::$connections[Connection::CONNECTION_DEFAULT];

        $connectionsList = self::$connections[Connection::CONNECTION_CONNECTIONS];

        if (!empty($connectionsList[self::$activeConnection][Connection::CONNECTION_DBNAME])) {
            self::$dbname = $connectionsList[self::$activeConnection][Connection::CONNECTION_DBNAME];
        }

        return new self();
    }
}
