<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

/**
 * Declare the lessThan method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait LessThanInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function lessThan(mixed $columnOrValue, mixed $value = null): self
    {
        if (null === $value) {
            if (self::$isSchema && self::$enableInsert) {
                self::addQueryList([
                    " < {$columnOrValue}",
                ]);
            } else {
                self::addRows([
                    $columnOrValue,
                ]);

                self::addQueryList([
                    ' < ?',
                ]);
            }

            return new self();
        }

        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([
                ' ',
                trim($columnOrValue),
                ' < ',
                $value,
            ]);
        } else {
            self::addRows([
                $value,
            ]);

            self::addQueryList([
                ' ',
                trim($columnOrValue . ' < ?'),
            ]);
        }

        return new self();
    }
}
