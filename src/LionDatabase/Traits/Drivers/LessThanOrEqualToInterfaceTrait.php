<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

/**
 * Declare the lessThanOrEqualTo method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait LessThanOrEqualToInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function lessThanOrEqualTo(string $column, mixed $lessThanOrEqualTo): self
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([
                ' ',
                trim($column),
                ' <= ',
                $lessThanOrEqualTo,
            ]);
        } else {
            self::addRows([
                $lessThanOrEqualTo,
            ]);

            self::addQueryList([
                ' ',
                trim($column . ' <= ?'),
            ]);
        }

        return new self();
    }
}
