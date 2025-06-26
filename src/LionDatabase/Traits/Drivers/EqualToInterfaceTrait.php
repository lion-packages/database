<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

/**
 * Declare the equalTo method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait EqualToInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function equalTo(string $column, mixed $equalTo): self
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([
                ' ',
                trim($column),
                ' = ',
                $equalTo,
            ]);
        } else {
            self::addRows([
                $equalTo,
            ]);

            self::addQueryList([
                ' ',
                trim($column . ' = ?'),
            ]);
        }

        return new self();
    }
}
