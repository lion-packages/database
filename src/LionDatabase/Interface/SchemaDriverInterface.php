<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

interface SchemaDriverInterface
{
    /**
     * Activate the configuration to run a process at the Schema
     * level in the service
     * */
    public static function isSchema(): object;

    /**
     * Enable the setting for nesting insert statements
     * */
    public static function enableInsert(bool $enable = false): object;
}
