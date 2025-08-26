<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Defines a method to add the 'less than or equal to / <=' operator.
 */
interface LessThanOrEqualToInterface
{
    /**
     * Adds a "less than or equal to / <=" to the current statement.
     *
     * This method can be used in two forms:
     * 1. `lessThanOrEqualTo(columnOrValue, value)` - adds a condition comparing a
     * column to a value.
     * 2. `lessThanOrEqualTo(value)` - adds a condition comparing the given value
     * (for expressions or literals).
     *
     * Only scalar values (`string`, `int`, `float`, `bool`) or `null` are allowed.
     * Passing objects, closures, or resources will result in a type error.
     *
     * <code>
     *     LessThanOrEqualToInterface::where('id')->lessThanOrEqualTo(1);
     * </code>
     *
     * <code>
     *     LessThanOrEqualToInterface::where()->lessThanOrEqualTo('id', 1);
     * </code>
     *
     * @param string|int|float|bool|null $columnOrValue The column name or the value
     * to compare.
     * @param string|int|float|bool|null $value Optional. The value to compare the
     * column against.
     *
     * @return self
     */
    public static function lessThanOrEqualTo(
        string|int|float|bool|null $columnOrValue,
        string|int|float|bool|null $value = null
    ): self;

}
