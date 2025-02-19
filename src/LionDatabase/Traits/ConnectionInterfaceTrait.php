<?php

declare(strict_types=1);

namespace Lion\Database\Traits;

use InvalidArgumentException;

/**
 * Declare the connection method of the interface
 *
 * @package Lion\Database\Traits
 */
trait ConnectionInterfaceTrait
{
    /**
     * {@inheritdoc}
     */
    public static function connection(string $connectionName): self
    {
        if (empty(self::$connections['connections'][$connectionName])) {
            throw new InvalidArgumentException('The selected connection does not exist', 500);
        }

        self::$activeConnection = $connectionName;

        self::$dbname = self::$connections['connections'][$connectionName]['dbname'];

        return new static();
    }
}
