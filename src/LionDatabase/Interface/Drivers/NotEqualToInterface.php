<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Defines a method to add the 'not equal to / <>' operator.
 */
interface NotEqualToInterface
{
    /**
     * Adds a "not equal to / <>" to the current statement.
     *
     * This method can be used in two forms:
     * 1. `notEqualTo(columnOrValue, value)` - adds a condition comparing a column
     * to a value.
     * 2. `notEqualTo(value)` - adds a condition comparing the given value (for
     * expressions or literals).
     *
     * Only scalar values (`string`, `int`, `float`, `bool`) or `null` are allowed.
     * Passing objects, closures, or resources will result in a type error.
     *
     * <code>
     *     NotEqualToInterface::where('id')->notEqualTo(1);
     * </code>
     *
     * <code>
     *     NotEqualToInterface::where()->notEqualTo('id', 1);
     * </code>
     *
     * @param string|int|float|bool|null $columnOrValue The column name or the value
     * to compare.
     * @param string|int|float|bool|null $value Optional. The value to compare the
     * column against.
     *
     * @return self
     */
    public static function notEqualTo(
        string|int|float|bool|null $columnOrValue,
        string|int|float|bool|null $value = null
    ): self;
}
