<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

use Lion\Database\Interface\DatabaseEngine\MySQLInterface;
use Lion\Database\Interface\DatabaseEngine\PostgreSQLInterface;

/**
 * Defines database engine configuration
 *
 * @package Lion\Database\Interface
 */
interface DatabaseEngineInterface extends MySQLInterface, PostgreSQLInterface
{
}
