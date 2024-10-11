<?php

declare(strict_types=1);

namespace Lion\Database\Drivers;

use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Database\Helpers\ConnectionInterfaceTrait;
use Lion\Database\Helpers\GetAllInterfaceTrait;
use Lion\Database\Helpers\GetInterfaceTrait;
use Lion\Database\Helpers\QueryInterfaceTrait;
use Lion\Database\Helpers\RunInterfaceTrait;
use Lion\Database\Helpers\TransactionInterfaceTrait;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Database\Interface\QueryInterface;
use Lion\Database\Interface\ReadDatabaseDataInterface;
use Lion\Database\Interface\RunDatabaseProcessesInterface;
use Lion\Database\Interface\TransactionInterface;
use stdClass;

/**
 * Provides an interface to build SQL queries dynamically in PHP applications
 * that interact with PostgreSQL databases.
 *
 * Key Features:
 *
 * * Intuitive methods: Simple methods to build SQL queries programmatically.
 * * SQL Injection Prevention: Helps prevent SQL injection attacks by sanitizing
 *   data entered in queries.
 * * Flexibility: Allows the construction of dynamic queries adapted to
 *   different application scenarios.
 * * Optimization for PostgreSQL: Designed specifically to work with PostgreSQL,
 *   guaranteeing compatibility and optimization with this DBMS.
 *
 * @property string $databaseMethod [Defines the database connection method to
 * use]
 *
 * @package Lion\Database\Drivers
 */
class PostgreSQL extends Connection implements
    DatabaseConfigInterface,
    QueryInterface,
    ReadDatabaseDataInterface,
    RunDatabaseProcessesInterface,
    TransactionInterface
{
    use ConnectionInterfaceTrait;
    use GetInterfaceTrait;
    use GetAllInterfaceTrait;
    use QueryInterfaceTrait;
    use RunInterfaceTrait;
    use TransactionInterfaceTrait;

    /**
     * Defines the database connection method to use
     *
     * This property determines which connection method to use in the `trait` to
     * perform database operations. Allowed values are `mysql` or `postgresql`,
     * depending on the database being used. The class using the `trait` must
     * set this value to define the connection type
     *
     * @var string $databaseMethod
     */
    private static string $databaseMethod = Driver::POSTGRESQL;

    /**
     * {@inheritdoc}
     */
    public static function execute(): stdClass
    {
        return parent::postgresql(function (): stdClass {
            $dataInfoKeys = array_keys(self::$dataInfo);

            if (count($dataInfoKeys) > 0) {
                self::$listSql = array_map(
                    fn ($value) => trim($value),
                    array_filter(explode(';', trim(self::$sql)), fn ($value) => trim($value) != '')
                );

                foreach ($dataInfoKeys as $key => $code) {
                    self::prepare(self::$listSql[$key]);

                    if (!empty(self::$dataInfo[$code])) {
                        self::bindValue($code);
                    }

                    self::$stmt->execute();

                    self::$stmt->closeCursor();
                }
            } else {
                self::prepare(self::$sql);

                self::$stmt->execute();
            }

            return (object) [
                'code' => 200,
                'status' => 'success',
                'message' => self::$message,
            ];
        });
    }
}
