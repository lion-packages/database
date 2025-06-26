<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

/**
 * Declare the notEqualTo method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait NotEqualToInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function notEqualTo(string $column, mixed $notEqualTo): self
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([
                ' ',
                trim($column),
                ' <> ',
                $notEqualTo,
            ]);
        } else {
            self::addRows([
                $notEqualTo,
            ]);

            self::addQueryList([
                ' ',
                trim($column . ' <> ?'),
            ]);
        }

        return new self();
    }
}
