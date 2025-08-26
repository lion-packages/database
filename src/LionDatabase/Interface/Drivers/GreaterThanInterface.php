<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Defines a method to add the 'greater than / >' operator.
 */
interface GreaterThanInterface
{
    /**
     * Adds a "greater than" (`>`) condition to the current query.
     *
     * This method can be used in two forms:
     * 1. `greaterThan(columnName, value)` - adds a condition comparing a column to
     * a value.
     * 2. `greaterThan(value)` - adds a condition comparing the given value (for
     * expressions or literals).
     *
     * Only scalar values (`string`, `int`, `float`, `bool`) or `null` are allowed.
     * Passing objects, closures, or resources will result in a type error.
     *
     * <code>
     *     GreaterThanInterface::where('id')->greaterThan(1);
     * </code>
     *
     * <code>
     *     GreaterThanInterface::where()->greaterThan('id', 1);
     * </code>
     *
     * @param string|int|float|bool|null $columnOrValue The column name or the value
     * to compare.
     * @param string|int|float|bool|null $value Optional. The value to compare the
     * column against.
     *
     * @return self Returns the current instance for method chaining.
     */
    public static function greaterThan(
        string|int|float|bool|null $columnOrValue,
        string|int|float|bool|null $value = null
    ): self;
}
