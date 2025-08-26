<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Defines a method to add the 'equal to / =' operator.
 */
interface EqualToInterface
{
    /**
     * Adds an equality condition (`=`) to the current query.
     *
     * This method can be used in two forms:
     * 1. `equalTo(columnName, value)` - adds a condition comparing a column to a
     * value.
     * 2. `equalTo(value)` - adds a condition comparing the given value (for
     * expressions or literals).
     *
     * Only scalar values (`string`, `int`, `float`, `bool`) or `null` are allowed.
     * Passing objects, closures, or resources will result in a type error.
     *
     * <code>
     *     EqualToInterface::where('id')->equalTo(1);
     * </code>
     *
     * <code>
     *      EqualToInterface::where()->equalTo('id', 1);
     * </code>
     *
     * @param string|int|float|bool|null $columnOrValue The column name or the value
     * to compare.
     * @param string|int|float|bool|null $value Optional. The value to compare the
     * column against.
     *
     * @return self Returns the current instance for method chaining.
     */
    public static function equalTo(
        string|int|float|bool|null $columnOrValue,
        string|int|float|bool|null $value = null
    ): self;
}
