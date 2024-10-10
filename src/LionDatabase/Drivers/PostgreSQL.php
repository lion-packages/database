<?php

declare(strict_types=1);

namespace Lion\Database\Drivers;

use InvalidArgumentException;
use Lion\Database\Connection;
use Lion\Database\Helpers\ConnectionInterfaceTrait;
use Lion\Database\Helpers\RunInterfaceTrait;
use Lion\Database\Interface\DatabaseCapsuleInterface;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Database\Interface\ReadDatabaseDataInterface;
use Lion\Database\Interface\RunDatabaseProcessesInterface;
use Lion\Database\Interface\TransactionInterface;
use PDO;
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
 * @package Lion\Database\Drivers
 */
class PostgreSQL extends Connection implements
    DatabaseConfigInterface,
    ReadDatabaseDataInterface,
    RunDatabaseProcessesInterface,
    TransactionInterface
{
    use ConnectionInterfaceTrait;
    use RunInterfaceTrait;

    /**
     * {@inheritdoc}
     */
    public static function get(): stdClass|array|DatabaseCapsuleInterface
    {
        return parent::postgresql(function (): stdClass|array|DatabaseCapsuleInterface {
            $responses = [];

            self::$listSql = array_map(
                fn ($value) => trim($value),
                array_filter(explode(';', trim(self::$sql)), fn ($value) => trim($value) != '')
            );

            $codes = array_keys(self::$fetchMode);

            foreach (self::$listSql as $key => $sql) {
                self::prepare($sql);

                $code = $codes[$key] ?? null;

                if ($code != null && isset(self::$dataInfo[$code])) {
                    self::bindValue($code);
                }

                if ($code != null && isset(self::$fetchMode[$code])) {
                    $getFetch = self::$fetchMode[$codes[$key]];

                    if (is_array($getFetch)) {
                        self::$stmt->setFetchMode($getFetch[0], $getFetch[1]);
                    } else {
                        self::$stmt->setFetchMode(self::$fetchMode[$codes[$key]]);
                    }
                }

                self::$stmt->execute();

                $request = self::$stmt->fetch();

                if (!$request) {
                    if (count(self::$fetchMode) > 1) {
                        $responses[] = (object) [
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'no data available',
                        ];
                    } else {
                        $responses = (object) [
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'no data available',
                        ];
                    }
                } else {
                    if (count(self::$fetchMode) > 1) {
                        $responses[] = $request;
                    } else {
                        $responses = $request;
                    }
                }
            }

            return $responses;
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function getAll(): stdClass|array
    {
        return parent::postgresql(function (): stdClass|array {
            $responses = [];

            self::$listSql = array_map(
                fn ($value) => trim($value),
                array_filter(explode(';', trim(self::$sql)), fn ($value) => trim($value) != '')
            );

            $codes = array_keys(self::$fetchMode);

            foreach (self::$listSql as $key => $sql) {
                self::prepare($sql);

                $code = isset($codes[$key]) ? $codes[$key] : null;

                if ($code != null && isset(self::$dataInfo[$code])) {
                    self::bindValue($code);
                }

                if ($code != null && isset(self::$fetchMode[$code])) {
                    $get_fetch = self::$fetchMode[$codes[$key]];

                    if (is_array($get_fetch)) {
                        self::$stmt->setFetchMode($get_fetch[0], $get_fetch[1]);
                    } else {
                        self::$stmt->setFetchMode(self::$fetchMode[$codes[$key]]);
                    }
                }

                self::$stmt->execute();

                $request = self::$stmt->fetchAll();

                if (!$request) {
                    if (count(self::$fetchMode) > 1) {
                        $responses[] = (object) [
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'no data available',
                        ];
                    } else {
                        $responses = (object) [
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'no data available',
                        ];
                    }
                } else {
                    if (count(self::$fetchMode) > 1) {
                        $responses[] = $request;
                    } else {
                        $responses = $request;
                    }
                }
            }

            return $responses;
        });
    }

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

    /**
     * {@inheritdoc}
     */
    public static function transaction(bool $isTransaction = true): PostgreSQL
    {
        self::$isTransaction = $isTransaction;

        return new static();
    }

    /**
     * The defined sentence alludes to the current sentence
     *
     * @param string $sql [Defined sentence]
     *
     * @return PostgreSQL
     */
    public static function query(string $sql): PostgreSQL
    {
        self::$actualCode = uniqid('code-');

        self::$dataInfo[self::$actualCode] = null;

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;

        self::addQueryList([$sql]);

        return new static();
    }
}
