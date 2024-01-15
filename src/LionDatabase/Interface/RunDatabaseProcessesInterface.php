<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

interface RunDatabaseProcessesInterface
{
    /**
     * Execute the current query
     * */
    public static function execute(): object;
}
