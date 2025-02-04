<?php

declare(strict_types=1);

namespace Lion\Database\Helpers\Interfaces;

use PDO;

/**
 * Declare the query method of the interface
 *
 * @package Lion\Database\Helpers\Interfaces
 */
trait QueryInterfaceTrait
{
    /**
     * {@inheritdoc}
     */
    public static function query(string $sql): self
    {
        self::$actualCode = uniqid('code-');

        self::$dataInfo[self::$actualCode] = null;

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;

        self::addQueryList([$sql]);

        return new static();
    }
}
