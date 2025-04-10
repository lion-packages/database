<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

use Lion\Database\Driver;

/**
 * Declare the table method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait TableInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function table(string|bool $table = true, bool $withDatabase = false): static
    {
        if (is_string($table)) {
            self::$table = !$withDatabase ? $table : self::$dbname . ".{$table}";
        } else {
            if ($table) {
                self::addQueryList([
                    self::getKey(Driver::MYSQL, 'table'),
                ]);
            }
        }

        return new static();
    }
}
