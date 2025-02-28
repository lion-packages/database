<?php

declare(strict_types=1);

namespace Lion\Database\Traits;

/**
 * Declare the rowCount method of the interface
 *
 * @package Lion\Database\Traits
 */
trait RowCountInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public function rowCount(): static
    {
        self::$withRowCount = true;

        return new static();
    }
}
