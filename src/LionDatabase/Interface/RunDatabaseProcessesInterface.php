<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

use PDOException;
use stdClass;

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
     * @return stdClass
     *
     * @throws PDOException [If the executed process fails]
     */
    public static function execute(): stdClass;
}
