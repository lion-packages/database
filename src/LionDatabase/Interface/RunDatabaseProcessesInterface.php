<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

/**
 * Defines that the driver can perform executions on the databases
 *
 * @package Lion\Database\Interface
 */
interface RunDatabaseProcessesInterface
{
    /**
     * Execute the current query
     *
     * @return object
     */
    public static function execute(): object;
}
