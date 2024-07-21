<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

/**
 * Defines a class as a capsule of an entity
 *
 * @package Lion\Database\Interface
 */
interface DatabaseCapsuleInterface
{
    /**
     * Returns the name of the entity.
     *
     * @return string
     */
    public function getTableName(): string;
}
