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
    public static function isSchema(): self
    {
        self::$isSchema = true;

        return new self();
    }

    /**
     * {@inheritdoc}
     */
    public static function enableInsert(bool $enable = false): self
    {
        self::$enableInsert = $enable;

        return new self();
    }
}
