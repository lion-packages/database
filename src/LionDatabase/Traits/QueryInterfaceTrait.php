<?php

declare(strict_types=1);

namespace Lion\Database\Traits;

use PDO;

/**
 * Declare the query method of the interface
 *
 * @package Lion\Database\Traits
 */
trait QueryInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function query(string $sql): static
    {
        self::$actualCode = uniqid('code-');

        self::$dataInfo[self::$actualCode] = null;

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;

        self::addQueryList([$sql]);

        return new static();
    }
}
