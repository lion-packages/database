<?php

declare(strict_types=1);

namespace Lion\Database\Traits;

use InvalidArgumentException;

/**
 * Declare the run method of the interface
 *
 * @package Lion\Database\Traits
 */
trait RunInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function run(array $connections): self
    {
        if (empty($connections['default'])) {
            throw new InvalidArgumentException('No default database defined', 500);
        }

        if (empty($connections['connections'])) {
            throw new InvalidArgumentException('No databases have been defined', 500);
        }

        self::$connections = $connections;

        self::$activeConnection = self::$connections['default'];

        if (!empty(self::$connections['connections'][self::$activeConnection]['dbname'])) {
            self::$dbname = self::$connections['connections'][self::$activeConnection]['dbname'];
        }

        return new static();
    }
}
