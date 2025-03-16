<?php

declare(strict_types=1);

namespace Lion\Database\Traits;

/**
 * Declare the transaction method of the interface
 *
 * @package Lion\Database\Traits
 */
trait TransactionInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function transaction(bool $isTransaction = true): static
    {
        self::$isTransaction = $isTransaction;

        return new static();
    }
}
