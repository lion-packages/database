<?php

declare(strict_types=1);

namespace Lion\Database\Drivers;

use Lion\Database\Connection;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Database\Interface\Drivers\AndInterface;
use Lion\Database\Interface\Drivers\BulkInterface;
use Lion\Database\Interface\Drivers\DatabaseInterface;
use Lion\Database\Interface\Drivers\DeleteInterface;
use Lion\Database\Interface\Drivers\EqualToInterface;
use Lion\Database\Interface\Drivers\GreaterThanInterface;
use Lion\Database\Interface\Drivers\GreaterThanOrEqualToInterface;
use Lion\Database\Interface\Drivers\InsertInterface;
use Lion\Database\Interface\Drivers\LessThanInterface;
use Lion\Database\Interface\Drivers\LessThanOrEqualToInterface;
use Lion\Database\Interface\Drivers\NotEqualToInterface;
use Lion\Database\Interface\Drivers\OnUpdateInterface;
use Lion\Database\Interface\Drivers\OrInterface;
use Lion\Database\Interface\Drivers\SelectInterface;
use Lion\Database\Interface\Drivers\TableInterface;
use Lion\Database\Interface\Drivers\UpdateInterface;
use Lion\Database\Interface\Drivers\WhereInterface;
use Lion\Database\Interface\GetQueryStringInterface;
use Lion\Database\Interface\QueryInterface;
use Lion\Database\Interface\ReadDatabaseDataInterface;
use Lion\Database\Interface\RowCountInterface;
use Lion\Database\Interface\ExecuteInterface;
use Lion\Database\Interface\SchemaDriverInterface;
use Lion\Database\Interface\TransactionInterface;
use Lion\Database\Traits\ConnectionInterfaceTrait;
use Lion\Database\Traits\Drivers\AndInterfaceTrait;
use Lion\Database\Traits\Drivers\BulkInterfaceTrait;
use Lion\Database\Traits\Drivers\DatabaseInterfaceTrait;
use Lion\Database\Traits\Drivers\DeleteInterfaceTrait;
use Lion\Database\Traits\Drivers\EqualToInterfaceTrait;
use Lion\Database\Traits\Drivers\GreaterThanInterfaceTrait;
use Lion\Database\Traits\Drivers\GreaterThanOrEqualToInterfaceTrait;
use Lion\Database\Traits\Drivers\InsertInterfaceTrait;
use Lion\Database\Traits\Drivers\LessThanInterfaceTrait;
use Lion\Database\Traits\Drivers\LessThanOrEqualToInterfaceTrait;
use Lion\Database\Traits\Drivers\NotEqualToInterfaceTrait;
use Lion\Database\Traits\Drivers\OnUpdateInterfaceTrait;
use Lion\Database\Traits\Drivers\OrInterfaceTrait;
use Lion\Database\Traits\Drivers\SelectInterfaceTrait;
use Lion\Database\Traits\Drivers\TableInterfaceTrait;
use Lion\Database\Traits\Drivers\UpdateInterfaceTrait;
use Lion\Database\Traits\Drivers\WhereInterfaceTrait;
use Lion\Database\Traits\ExecuteInterfaceTrait;
use Lion\Database\Traits\GetAllInterfaceTrait;
use Lion\Database\Traits\GetInterfaceTrait;
use Lion\Database\Traits\GetQueryStringInterfaceTrait;
use Lion\Database\Traits\QueryInterfaceTrait;
use Lion\Database\Traits\RowCountInterfaceTrait;
use Lion\Database\Traits\RunInterfaceTrait;
use Lion\Database\Traits\SchemaDriverInterfaceTrait;
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
 * @package Lion\Database\Drivers
 */
class PostgreSQL extends Connection implements
    AndInterface,
    BulkInterface,
    DatabaseConfigInterface,
    DatabaseInterface,
    DeleteInterface,
    EqualToInterface,
    GetQueryStringInterface,
    GreaterThanInterface,
    GreaterThanOrEqualToInterface,
    InsertInterface,
    NotEqualToInterface,
    LessThanInterface,
    LessThanOrEqualToInterface,
    OnUpdateInterface,
    OrInterface,
    QueryInterface,
    ReadDatabaseDataInterface,
    RowCountInterface,
    ExecuteInterface,
    SchemaDriverInterface,
    SelectInterface,
    TableInterface,
    TransactionInterface,
    UpdateInterface,
    WhereInterface
{
    use AndInterfaceTrait;
    use BulkInterfaceTrait;
    use ConnectionInterfaceTrait;
    use DatabaseInterfaceTrait;
    use DeleteInterfaceTrait;
    use EqualToInterfaceTrait;
    use ExecuteInterfaceTrait;
    use GetQueryStringInterfaceTrait;
    use GetInterfaceTrait;
    use GetAllInterfaceTrait;
    use GreaterThanInterfaceTrait;
    use GreaterThanOrEqualToInterfaceTrait;
    use InsertInterfaceTrait;
    use NotEqualToInterfaceTrait;
    use LessThanInterfaceTrait;
    use LessThanOrEqualToInterfaceTrait;
    use OnUpdateInterfaceTrait;
    use OrInterfaceTrait;
    use QueryInterfaceTrait;
    use RowCountInterfaceTrait;
    use RunInterfaceTrait;
    use SchemaDriverInterfaceTrait;
    use SelectInterfaceTrait;
    use TableInterfaceTrait;
    use TransactionInterfaceTrait;
    use UpdateInterfaceTrait;
    use WhereInterfaceTrait;
}
