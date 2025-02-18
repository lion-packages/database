<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

use Lion\Database\Driver;
use PDO;

/**
 * Declare the select method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait SelectInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function select(): static
    {
        if (empty(self::$actualCode)) {
            self::$actualCode = uniqid('code-');
        }

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;

        $stringColumns = self::addColumns(func_get_args());

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'select'),
            " {$stringColumns}",
            self::getKey(Driver::MYSQL, 'from'),
            ' ',
            ('' === self::$table ? self::$view : self::$table),
        ]);

        return new static();
    }
}
