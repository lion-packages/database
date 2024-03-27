<?php

declare(strict_types=1);

namespace Lion\Database\Helpers;

use Lion\Database\Driver;
use Lion\Database\Helpers\Constants\MySQLConstants;

/**
 * Defines functions to obtain data from available dictionaries
 *
 * @package Lion\Database\Helpers
 */
trait KeywordsTrait
{
    /**
     * [List of available dictionaries]
     *
     * @const DATABASE_KEYWORDS
     */
    private const DATABASE_KEYWORDS = [
        Driver::MYSQL => MySQLConstants::KEYWORDS
    ];

    /**
     * Get a value from a dictionary
     *
     * @param string $type [Define the dictionary]
     * @param string $key [Value to look up in the dictionary]
     *
     * @return string|null
     */
    public static function getKey(string $dictionary, string $key): ?string
    {
        return self::DATABASE_KEYWORDS[$dictionary][$key] ?? null;
    }
}
