<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

/**
 * Defines a driver with transaction functions
 *
 * @package Lion\Database\Interface
 */
interface TransactionInterface
{
    /**
     * Activate the configuration to execute a transaction type process in the
     * service
     *
     * @param bool $isTransaction [Defines whether the process is a transaction]
     *
     * @return object
     */
    public static function transaction(bool $isTransaction = true): object;
}
