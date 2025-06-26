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
    public static function lessThanOrEqualTo(mixed $columnOrValue, mixed $value = null): self
    {
        if (null === $value) {
            if (self::$isSchema && self::$enableInsert) {
                self::addQueryList([
                    " <= {$columnOrValue}",
                ]);
            } else {
                self::addRows([
                    $columnOrValue,
                ]);

                self::addQueryList([
                    ' <= ?',
                ]);
            }

            return new self();
        }

        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([
                ' ',
                trim($columnOrValue),
                ' <= ',
                $value,
            ]);
        } else {
            self::addRows([
                $value,
            ]);

            self::addQueryList([
                ' ',
                trim($columnOrValue . ' <= ?'),
            ]);
        }

        return new self();
    }
}
