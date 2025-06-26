<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Defines a method to add the 'equal to / =' operator
 *
 * @package Lion\Database\Interface\Drivers
 */
interface EqualToInterface
{
    /**
     * Adds an "equals to / =" to the current statement
     *
     * @param string $columnOrValue Column name or value to be equal to
     * @param mixed $value Value to which it is equal
     *
     * @return self
     */
    public static function equalTo(mixed $columnOrValue, mixed $value = null): self;
}
