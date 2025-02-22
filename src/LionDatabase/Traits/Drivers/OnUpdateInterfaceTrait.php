<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

use Lion\Database\Driver;

/**
 * Declare the onUpdate method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait OnUpdateInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function onUpdate(?string $onUpdate = null): static
    {
        if (empty($onUpdate)) {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'on'),
                self::getKey(Driver::MYSQL, 'update')
            ]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'on'),
                self::getKey(Driver::MYSQL, 'update'),
                ' ',
                $onUpdate,
            ]);
        }

        return new static();
    }
}
