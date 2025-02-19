<?php

declare(strict_types=1);

namespace Lion\Database\Traits;

/**
 * Declare the transaction method of the interface
 *
 * @package Lion\Database\Traits
 */
trait SchemaDriverInterfaceTrait
{
    /**
     * {@inheritdoc}
     */
    public static function isSchema(): static
    {
        self::$isSchema = true;

        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public static function enableInsert(bool $enable = false): static
    {
        self::$enableInsert = $enable;

        return new static();
    }
}
