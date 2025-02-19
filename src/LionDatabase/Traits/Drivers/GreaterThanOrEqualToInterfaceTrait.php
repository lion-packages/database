<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

/**
 * Declare the greaterThanOrEqualTo method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait GreaterThanOrEqualToInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function greaterThanOrEqualTo(string $column, mixed $greaterThanOrEqualTo): static
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([
                ' ',
                trim($column),
                ' >= ',
                $greaterThanOrEqualTo,
            ]);
        } else {
            self::addRows([
                $greaterThanOrEqualTo,
            ]);

            self::addQueryList([
                ' ',
                trim($column . ' >= ?'),
            ]);
        }

        return new static();
    }
}
