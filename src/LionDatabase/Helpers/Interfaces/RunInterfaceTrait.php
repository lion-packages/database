<?php

declare(strict_types = 1);

namespace Lion\Database\Helpers\Interfaces;

use InvalidArgumentException;

/**
 * Declare the run method of the interface
 *
 * @package Lion\Database\Helpers
 */
trait RunInterfaceTrait
{
    /**
     * {@inheritdoc}
     */
    public static function run(array $connections): self
    {
        if (empty($connections['default'])) {
            throw new InvalidArgumentException('no default database defined', 500);
        }

        if (empty($connections['connections'])) {
            throw new InvalidArgumentException('no databases have been defined', 500);
        }

        self::$connections = $connections;

        self::$activeConnection = self::$connections['default'];

        self::$dbname = self::$connections['connections'][self::$connections['default']]['dbname'];

        return new static();
    }
}
