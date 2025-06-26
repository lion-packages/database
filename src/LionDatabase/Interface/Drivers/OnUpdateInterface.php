<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Add ON UPDATE function in SQL query
 *
 * @package Lion\Database\Interface\Drivers
 */
interface OnUpdateInterface
{
    /**
     * Nests the ON UPDATE statement in the current query
     *
     * @param string|null $onUpdate [Nested parameter in ON UPDATE]
     *
     * @return self
     */
    public static function onUpdate(?string $onUpdate = null): self;
}
