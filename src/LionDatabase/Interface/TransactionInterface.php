<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

interface TransactionInterface
{
    /**
     * Activate the configuration to execute a transaction type
     * process in the service
     * */
    public static function transaction(bool $isTransaction = true): object;
}
