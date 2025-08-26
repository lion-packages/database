<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

/**
 * Declare the greaterThan method of the interface.
 */
trait GreaterThanInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function greaterThan(
        string|int|float|bool|null $columnOrValue,
        string|int|float|bool|null $value = null
    ): self {
        if (null === $value) {
            if (self::$isSchema && self::$enableInsert) {
                self::addQueryList([
                    " > {$columnOrValue}",
                ]);
            } else {
                self::addRows([
                    $columnOrValue,
                ]);

                self::addQueryList([
                    ' > ?',
                ]);
            }

            return new self();
        }

        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([
                ' ',
                trim($columnOrValue),
                ' > ',
                $value,
            ]);
        } else {
            self::addRows([
                $value,
            ]);

            self::addQueryList([
                ' ',
                trim($columnOrValue . ' > ?'),
            ]);
        }

        return new self();
    }
}
