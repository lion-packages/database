<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

use stdClass;

/**
 * Generic function to obtain information about the current sentence
 *
 * @package Lion\Database\Interface
 */
interface GetQueryStringInterface
{
    /**
     * Gets an object with the current statement
     *
     * @return stdClass
     */
    public static function getQueryString(): stdClass;
}
