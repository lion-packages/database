<?php

declare(strict_types=1);

namespace Lion\Database\Drivers;

use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Database\Interface\Drivers\AndInterface;
use Lion\Database\Interface\Drivers\DeleteInterface;
use Lion\Database\Interface\Drivers\InsertInterface;
use Lion\Database\Interface\Drivers\SelectInterface;
use Lion\Database\Interface\Drivers\TableInterface;
use Lion\Database\Interface\Drivers\UpdateInterface;
use Lion\Database\Interface\Drivers\WhereInterface;
use Lion\Database\Interface\QueryInterface;
use Lion\Database\Interface\ReadDatabaseDataInterface;
use Lion\Database\Interface\RunDatabaseProcessesInterface;
use Lion\Database\Interface\TransactionInterface;
use Lion\Database\Traits\ConnectionInterfaceTrait;
use Lion\Database\Traits\Drivers\AndInterfaceTrait;
use Lion\Database\Traits\Drivers\DeleteInterfaceTrait;
use Lion\Database\Traits\Drivers\InsertInterfaceTrait;
use Lion\Database\Traits\Drivers\SelectInterfaceTrait;
use Lion\Database\Traits\Drivers\TableInterfaceTrait;
use Lion\Database\Traits\Drivers\UpdateInterfaceTrait;
use Lion\Database\Traits\Drivers\WhereInterfaceTrait;
use Lion\Database\Traits\ExecuteInterfaceTrait;
use Lion\Database\Traits\GetAllInterfaceTrait;
use Lion\Database\Traits\GetInterfaceTrait;
use Lion\Database\Traits\QueryInterfaceTrait;
use Lion\Database\Traits\RunInterfaceTrait;
use Lion\Database\Traits\TransactionInterfaceTrait;

/**
 * Provides an interface to build SQL queries dynamically in PHP applications
 * that interact with PostgreSQL databases
 *
 * Key Features:
 *
 * * Intuitive methods: Simple methods to build SQL queries programmatically
 * * SQL Injection Prevention: Helps prevent SQL injection attacks by sanitizing
 *   data entered in queries
 * * Flexibility: Allows the construction of dynamic queries adapted to
 *   different application scenarios
 * * Optimization for PostgreSQL: Designed specifically to work with PostgreSQL,
 *   guaranteeing compatibility and optimization with this DBMS
 *
 * @property string $databaseMethod [Defines the database connection method to
 * use]
 *
 * @package Lion\Database\Drivers
 */
class PostgreSQL extends Connection implements
    AndInterface,
    DatabaseConfigInterface,
    DeleteInterface,
    InsertInterface,
    QueryInterface,
    ReadDatabaseDataInterface,
    RunDatabaseProcessesInterface,
    SelectInterface,
    TableInterface,
    TransactionInterface,
    UpdateInterface,
    WhereInterface
{
    use AndInterfaceTrait;
    use ConnectionInterfaceTrait;
    use DeleteInterfaceTrait;
    use ExecuteInterfaceTrait;
    use GetInterfaceTrait;
    use GetAllInterfaceTrait;
    use InsertInterfaceTrait;
    use QueryInterfaceTrait;
    use RunInterfaceTrait;
    use SelectInterfaceTrait;
    use TableInterfaceTrait;
    use TransactionInterfaceTrait;
    use UpdateInterfaceTrait;
    use WhereInterfaceTrait;

    /**
     * Defines the database connection method to use
     *
     * This property determines which connection method to use in the `trait` to
     * perform database operations. Allowed values are `mysql` or `postgresql`,
     * depending on the database being used. The class using the `trait` must
     * set this value to define the connection type
     *
     * @var string $databaseMethod
     *
     * @phpstan-ignore-next-line
     */
    private static string $databaseMethod = Driver::POSTGRESQL;
}
