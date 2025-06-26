<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

/**
 * Defines the configuration to determine that a driver has schematic functions
 *
 * @package Lion\Database\Interface
 */
interface SchemaDriverInterface
{
    /**
     * Activate the configuration to run a process at the Schema level in the
     * service
     *
     * @return self
     */
    public static function isSchema(): self;

    /**
     * Enable the setting for nesting insert statements
     *
     * @param bool $enable Defines whether the values integrated into bindValue are
     * concatenated
     *
     * @return self
     */
    public static function enableInsert(bool $enable = false): self;
}
