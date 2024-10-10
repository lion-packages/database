<?php

declare(strict_types=1);

namespace Lion\Database\Helpers;

use InvalidArgumentException;

/**
 * Declare the connection method of the interface
 *
 * @package Lion\Database\Helpers
 */
trait ConnectionInterfaceTrait
{
    /**
     * {@inheritdoc}
     */
    public static function connection(string $connectionName): self
    {
        if (empty(self::$connections['connections'][$connectionName])) {
            throw new InvalidArgumentException('the selected connection does not exist', 500);
        }

        self::$activeConnection = $connectionName;

        self::$dbname = self::$connections['connections'][$connectionName]['dbname'];

        return new static();
    }
}
