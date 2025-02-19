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
     * {@inheritdoc}
     */
    public static function transaction(bool $isTransaction = true): self
    {
        self::$isTransaction = $isTransaction;

        return new static();
    }
}
