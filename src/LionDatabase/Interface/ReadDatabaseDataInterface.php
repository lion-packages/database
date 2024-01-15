<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

interface ReadDatabaseDataInterface
{
    /**
     * Run and get an object from a row
     * */
    public static function get(): array|object;

    /**
     * Run and get an array of objects
     * */
    public static function getAll(): array|object;
}
