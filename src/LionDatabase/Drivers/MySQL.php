<?php

declare(strict_types=1);

namespace Lion\Database\Drivers;

use Closure;
use InvalidArgumentException;
use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Database\Interface\DatabaseCapsuleInterface;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Database\Interface\ReadDatabaseDataInterface;
use Lion\Database\Interface\RunDatabaseProcessesInterface;
use Lion\Database\Interface\SchemaDriverInterface;
use Lion\Database\Interface\TransactionInterface;
use PDO;
use stdClass;

/**
 * Provides an interface to build SQL queries dynamically in PHP applications
 * that interact with MySQL databases
 *
 * Key Features:
 *
 * * Intuitive methods: Simple methods to build SQL queries programmatically
 * * SQL Injection Prevention: Helps prevent SQL injection attacks by sanitizing
 * data entered in queries
 * * Flexibility: Allows the construction of dynamic queries adapted to
 * different application scenarios
 * * Optimization for MySQL: Designed specifically to work with MySQL,
 * guaranteeing compatibility and optimization with this DBMS
 *
 * @package Lion\Database\Drivers
 */
class MySQL extends Connection implements
    DatabaseConfigInterface,
    ReadDatabaseDataInterface,
    RunDatabaseProcessesInterface,
    SchemaDriverInterface,
    TransactionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function run(array $connections): MySQL
    {
        if (empty($connections['default'])) {
            throw new InvalidArgumentException('no default database defined', 500);
        }

        if (empty($connections['connections'])) {
            throw new InvalidArgumentException('no databases have been defined', 500);
        }

        self::$connections = $connections;

        self::$activeConnection = self::$connections['default'];

        self::$dbname = self::$connections['connections'][self::$connections['default']]['dbname'];

        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public static function connection(string $connectionName): MySQL
    {
        if (empty(self::$connections['connections'][$connectionName])) {
            throw new InvalidArgumentException('the selected connection does not exist', 500);
        }

        self::$activeConnection = $connectionName;

        self::$dbname = self::$connections['connections'][$connectionName]['dbname'];

        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public static function transaction(bool $isTransaction = true): MySQL
    {
        self::$isTransaction = $isTransaction;

        return new static;
    }

    /**
     * {@inheritdoc}
     */
    public static function isSchema(): MySQL
    {
        self::$isSchema = true;

        return new static;
    }

    /**
     * {@inheritdoc}
     */
    public static function enableInsert(bool $enable = false): MySQL
    {
        self::$enableInsert = $enable;

        return new static;
    }

    /**
     * {@inheritdoc}
     */
    public static function get(): stdClass|array|DatabaseCapsuleInterface
    {
        return parent::mysql(function (): stdClass|array|DatabaseCapsuleInterface {
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

            self::clean();

            return $responses;
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function getAll(): stdClass|array
    {
        return parent::mysql(function (): stdClass|array {
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

            self::clean();

            return $responses;
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function execute(): stdClass
    {
        return parent::mysql(function (): stdClass {
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

                if (!empty(self::$actualCode)) {
                    self::bindValue(self::$actualCode);
                }

                self::$stmt->execute();
            }

            self::clean();

            return (object) [
                'code' => 200,
                'status' => 'success',
                'message' => self::$message,
            ];
        });
    }

    public static function database(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'database')]);

        return new static;
    }

    public static function truncate(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'truncate')]);

        return new static;
    }

    public static function autoIncrement(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'auto-increment')]);

        return new static;
    }

    public static function action(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'action')]);

        return new static;
    }

    public static function no(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'no')]);

        return new static;
    }

    public static function cascade(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'cascade')]);

        return new static;
    }

    public static function restrict(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'restrict')]);

        return new static;
    }

    public static function onDelete(): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'on'),
            self::getKey(Driver::MYSQL, 'delete')
        ]);

        return new static;
    }

    public static function onUpdate(): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'on'),
            self::getKey(Driver::MYSQL, 'update')
        ]);

        return new static;
    }

    public static function on(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'on')]);

        return new static;
    }

    public static function references(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'references')]);

        return new static;
    }

    public static function foreign(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'foreign')]);

        return new static;
    }

    public static function constraint(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'constraint')]);

        return new static;
    }

    public static function add(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'add')]);

        return new static;
    }

    public static function alter(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'alter')]);

        return new static;
    }

    public static function comment(string $value = ''): MySQL
    {
        if ('' === $value) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'comment')]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'comment'),
                ' ',
                "'{$value}'"
            ]);
        }

        return new static;
    }

    public static function unique(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'unique')]);

        return new static;
    }

    public static function primaryKey(string $value): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'primary'),
            self::getKey(Driver::MYSQL, 'key'),
            " ({$value})"
        ]);

        return new static;
    }

    public static function key(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'key')]);

        return new static;
    }

    public static function primary(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'primary')]);

        return new static;
    }

    public static function engine(string $value = ''): MySQL
    {
        if ('' === $value) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'engine')]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'engine'),
                ' = ',
                $value
            ]);
        }

        return new static;
    }

    public static function notNull(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'not-null')]);

        return new static;
    }

    public static function null(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'null')]);

        return new static;
    }

    public static function innoDB(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'innodb')]);

        return new static;
    }

    public static function collate(string $value = ''): MySQL
    {
        if ('' === $value) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'collate')]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'collate'),
                ' = ',
                $value
            ]);
        }

        return new static;
    }

    public static function set(string $value = ''): MySQL
    {
        if ('' === $value) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'set')]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'set'),
                ' = ',
                $value
            ]);
        }

        return new static;
    }

    public static function character(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'character')]);

        return new static;
    }

    public static function default(string|int $value = ''): MySQL
    {
        if ('' === $value) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'default')]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'default'),
                ' ',
                (is_string($value) ? "'{$value}'" : $value)
            ]);
        }

        return new static;
    }

    public static function schema(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'schema')]);

        return new static;
    }

    public static function addQuery(string $query): MySQL
    {
        self::addQueryList([" {$query}"]);

        return new static;
    }

    public static function ifExists(string $exist): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'exists'),
            " `{$exist}`"
        ]);

        return new static;
    }

    public static function ifNotExists(string $notExist): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'not'),
            self::getKey(Driver::MYSQL, 'exists'),
            " `{$notExist}`"
        ]);

        return new static;
    }

    public static function use(string $use): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `{$use}`"
        ]);

        return new static;
    }

    public static function begin(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'begin')]);

        return new static;
    }

    public static function end(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'end')]);

        return new static;
    }

    public static function create(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'create')]);

        return new static;
    }

    public static function procedure(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'procedure')]);

        return new static;
    }

    public static function status(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'status')]);

        return new static;
    }

    public static function closeQuery(string $close = ';'): MySQL
    {
        self::addQueryList([$close]);

        return new static;
    }

    public static function full(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'full')]);

        return new static;
    }

    public static function groupQuery(Closure $callback): MySQL
    {
        self::openGroup();

        $callback();

        self::closeGroup();

        return new static;
    }

    public static function recursive(string $name): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'recursive'),
            " {$name}",
            self::getKey(Driver::MYSQL, 'as')
        ]);

        return new static;
    }

    public static function with(bool $isString = false): MySQL|string
    {
        if ($isString) {
            return self::getKey(Driver::MYSQL, 'with');
        }

        self::addQueryList([self::getKey(Driver::MYSQL, 'with')]);

        return new static;
    }

    public static function table(string|bool $table = true, bool $withDatabase = true): MySQL
    {
        if (is_string($table)) {
            self::$table = !$withDatabase ? $table : self::$dbname . ".{$table}";
        } else {
            if ($table) {
                self::addQueryList([self::getKey(Driver::MYSQL, 'table')]);
            }
        }

        return new static;
    }

    public static function view(string|bool $view = true, bool $withDatabase = true): MySQL
    {
        if (is_string($view)) {
            self::$view = !$withDatabase ? $view : self::$dbname . ".{$view}";
        } else {
            if ($view) {
                self::addQueryList([self::getKey(Driver::MYSQL, 'view')]);
            }
        }

        return new static;
    }

    public static function isNull(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'is-null')]);

        return new static;
    }

    public static function isNotNull(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'is-not-null')]);

        return new static;
    }

    public static function offset(int $increase = 0): MySQL
    {
        self::addRows([$increase]);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'offset'),
            ' ?'
        ]);

        return new static;
    }

    public static function unionAll(): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'union'),
            self::getKey(Driver::MYSQL, 'all')
        ]);

        return new static;
    }

    public static function union(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'union')]);

        return new static;
    }

    public static function as(string $column, string $as): string
    {
        return $column . self::getKey(Driver::MYSQL, 'as') . " {$as}";
    }

    public static function concat()
    {
        return str_replace('*', implode(', ', func_get_args()), self::getKey(Driver::MYSQL, 'concat'));
    }

    public static function createTable(): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'create'),
            self::getKey(Driver::MYSQL, 'table'),
            ' ',
            self::$table
        ]);

        return new static;
    }

    public static function show(): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid('code-');
        }

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;

        self::$sql = self::getKey(Driver::MYSQL, 'show');

        return new static;
    }

    public static function from(string $from = null): MySQL
    {
        if (null === $from) {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'from'),
                ' ',
                ('' === trim(self::$table) ? self::$view : self::$table)
            ]);
        } else {
            self::addQueryList([self::getKey(Driver::MYSQL, 'from'), ' ', $from]);
        }

        return new static;
    }

    public static function index(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'index')]);

        return new static;
    }

    public static function drop(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'drop')]);

        return new static;
    }

    public static function constraints(): MySQL
    {
        self::addRows(explode('.', self::$table));

        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'select'),
            ' CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME',
            self::getKey(Driver::MYSQL, 'from'),
            ' information_schema.KEY_COLUMN_USAGE WHERE ',
            'TABLE_SCHEMA=? AND TABLE_NAME=? AND REFERENCED_COLUMN_NAME IS NOT NULL'
        ]);

        return new static;
    }

    public static function tables(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'tables')]);

        return new static;
    }

    public static function columns(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'columns')]);

        return new static;
    }

    public static function query(string $sql): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid('code-');
        }

        self::addQueryList([$sql]);

        return new static;
    }

    public static function bulk(array $columns, array $rows): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid('code-');
        }

        self::$message = 'Rows inserted successfully';

        foreach ($rows as $row) {
            self::addRows($row);
        }

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'insert'),
            self::getKey(Driver::MYSQL, 'into'),
            ' ',
            self::$table,
            ' (',
            self::addColumns($columns),
            ')',
            self::getKey(Driver::MYSQL, 'values'),
            ' ',
            self::addCharacterBulk($rows, (self::$isSchema && self::$enableInsert))
        ]);

        return new static;
    }

    public static function in(?array $values = null): MySQL
    {
        if (is_array($values)) {
            self::addRows($values);

            self::addQueryList([
                str_replace('?', self::addCharacter($values), self::getKey(Driver::MYSQL, 'in'))
            ]);
        } else {
            self::addQueryList([str_replace("(?)", '', self::getKey(Driver::MYSQL, 'in'))]);
        }

        return new static;
    }

    public static function call(string $storeProcedure, array $rows = []): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid('code-');
        }

        self::$message = 'Procedure executed successfully';

        self::addRows($rows);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'call'),
            ' ',
            self::$dbname,
            ".{$storeProcedure}(",
            self::addCharacter($rows),
            ")"
        ]);

        return new static;
    }

    public static function delete(): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid('code-');
        }

        self::$message = 'Rows deleted successfully';

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'delete'),
            self::getKey(Driver::MYSQL, 'from'),
            ' ',
            self::$table
        ]);

        return new static;
    }

    public static function update(array $rows = []): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid('code-');
        }

        self::$message = 'Rows updated successfully';

        self::addRows($rows);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'update'),
            ' ',
            self::$table,
            self::getKey(Driver::MYSQL, 'set'),
            ' ',
            self::addCharacterEqualTo($rows)
        ]);

        return new static;
    }

    public static function insert(array $rows = []): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid('code-');
        }

        self::$message = 'Rows inserted successfully';

        self::addRows($rows);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'insert'),
            self::getKey(Driver::MYSQL, 'into'),
            ' ',
            self::$table,
            ' (',
            self::addColumns(array_keys($rows)),
            ')',
            self::getKey(Driver::MYSQL, 'values'),
            ' (',
            (
                !self::$isSchema
                ? self::addCharacterAssoc($rows)
                : self::addColumns(
                    array_values($rows),
                    true,
                    (self::$isSchema && self::$enableInsert && self::$isProcedure ? false : true)
                )
            ),
            ')'
        ]);

        return new static;
    }

    public static function having(string $column, mixed $value): MySQL
    {
        self::addRows([$value]);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'having'),
            " {$column}"
        ]);

        return new static;
    }

    public static function select(): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid('code-');
        }

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;

        $stringColumns = self::addColumns(func_get_args());

        if ('' === self::$table) {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'select'),
                " {$stringColumns}",
                self::getKey(Driver::MYSQL, 'from'),
                ' ',
                self::$view
            ]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'select'),
                " {$stringColumns}",
                self::getKey(Driver::MYSQL, 'from'),
                ' ',
                self::$table
            ]);
        }

        return new static;
    }

    public static function selectDistinct(): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid('code-');
        }

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;

        $stringColumns = self::addColumns(func_get_args());

        if (empty(self::$table)) {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'select'),
                self::getKey(Driver::MYSQL, 'distinct'),
                " {$stringColumns}",
                self::getKey(Driver::MYSQL, 'from'),
                ' ',
                self::$view
            ]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'select'),
                self::getKey(Driver::MYSQL, 'distinct'),
                " {$stringColumns}",
                self::getKey(Driver::MYSQL, 'from'),
                ' ',
                self::$table
            ]);
        }

        return new static;
    }

    public static function between(mixed $between, mixed $and): MySQL
    {
        self::addRows([$between, $and]);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'between'),
            ' ?',
            self::getKey(Driver::MYSQL, 'and'),
            ' ? '
        ]);

        return new static;
    }

    public static function like(string $like): MySQL
    {
        self::addRows([$like]);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'like'),
            ' ',
            self::addCharacter([$like])
        ]);

        return new static;
    }

    public static function groupBy(): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'group-by'),
            ' ',
            self::addColumns(func_get_args())
        ]);

        return new static;
    }

    public static function limit(int $start, ?int $limit = null): MySQL
    {
        $items = [$start];

        self::addRows([$start]);

        if (null != $limit) {
            $items[] = $limit;

            self::addRows([$limit]);
        }

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'limit'),
            ' ',
            self::addCharacter($items)
        ]);

        return new static;
    }

    public static function asc(bool $isString = false): MySQL|string
    {
        if ($isString) {
            return self::getKey(Driver::MYSQL, 'asc');
        }

        self::addQueryList([self::getKey(Driver::MYSQL, 'asc')]);

        return new static;
    }

    public static function desc(bool $isString = false): MySQL|string
    {
        if ($isString) {
            return self::getKey(Driver::MYSQL, 'desc');
        }

        self::addQueryList([self::getKey(Driver::MYSQL, 'desc')]);

        return new static;
    }

    public static function orderBy(): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'order-by'),
            ' ',
            self::addColumns(func_get_args())
        ]);

        return new static;
    }

    public static function inner(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'inner')]);

        return new static;
    }

    public static function left(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'left')]);

        return new static;
    }

    public static function right(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'right')]);

        return new static;
    }

    public static function join(string $table, string $valueFrom, string $valueUpTo, bool $withAlias = true): MySQL
    {
        if ($withAlias) {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'join'),
                ' ',
                self::$dbname,
                ".{$table}",
                self::getKey(Driver::MYSQL, 'on'),
                " {$valueFrom} = {$valueUpTo}"
            ]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'join'),
                " {$table}",
                self::getKey(Driver::MYSQL, 'on'),
                " {$valueFrom} = {$valueUpTo}"
            ]);
        }

        return new static;
    }

    public static function where(Closure|string|bool $valueType = true): MySQL
    {
        if (is_callable($valueType)) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'where')]);

            $valueType();
        } elseif (is_string($valueType)) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'where'), " {$valueType}"]);
        } else {
            if ($valueType) {
                self::addQueryList([self::getKey(Driver::MYSQL, 'where')]);
            }
        }

        return new static;
    }

    public static function and(Closure|string|bool $valueType = true): MySQL
    {
        if (is_callable($valueType)) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'and')]);

            $valueType();
        } elseif (is_string($valueType)) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'and'), " {$valueType}"]);
        } else {
            if ($valueType) {
                self::addQueryList([self::getKey(Driver::MYSQL, 'and')]);
            }
        }

        return new static;
    }

    public static function or(Closure|string|bool $valueType = true): MySQL
    {
        if (is_callable($valueType)) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'or')]);

            $valueType();
        } elseif (is_string($valueType)) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'or'), " {$valueType}"]);
        } else {
            if ($valueType) {
                self::addQueryList([self::getKey(Driver::MYSQL, 'or')]);
            }
        }

        return new static;
    }

    public static function getColumn(string $column, string $table = ''): string
    {
        return '' === $table ? trim($column) : trim("{$table}.{$column}");
    }

    public static function column(string $column, string $table = ''): MySQL
    {
        self::addQueryList('' === $table ? [' ', trim($column)] : [' ', trim("{$table}.{$column}")]);

        return new static;
    }

    public static function equalTo(string $column, mixed $value): MySQL
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([' ', trim($column), ' = ', trim($value)]);
        } else {
            self::addRows([$value]);

            self::addQueryList([' ', trim($column . ' = ?')]);
        }

        return new static;
    }

    public static function notEqualTo(string $column, mixed $value): MySQL
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([' ', trim($column), ' <> ', trim($value)]);
        } else {
            self::addRows([$value]);

            self::addQueryList([' ', trim($column . ' <> ?')]);
        }

        return new static;
    }

    public static function greaterThan(string $column, mixed $value): MySQL
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([' ', trim($column), ' > ', trim($value)]);
        } else {
            self::addRows([$value]);

            self::addQueryList([' ', trim($column . ' > ?')]);
        }

        return new static;
    }

    public static function lessThan(string $column, mixed $value): MySQL
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([' ', trim($column), ' < ', trim($value)]);
        } else {
            self::addRows([$value]);

            self::addQueryList([' ', trim($column . ' < ?')]);
        }

        return new static;
    }

    public static function greaterThanOrEqualTo(string $column, mixed $value): MySQL
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([' ', trim($column), ' >= ', trim($value)]);
        } else {
            self::addRows([$value]);

            self::addQueryList([' ', trim($column . ' >= ?')]);
        }

        return new static;
    }

    public static function lessThanOrEqualTo(string $column, mixed $value): MySQL
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([' ', trim($column), ' <= ', trim($value)]);
        } else {
            self::addRows([$value]);

            self::addQueryList([' ', trim($column . ' <= ?')]);
        }

        return new static;
    }

    public static function min(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'min')));
    }

    public static function max(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'max')));
    }

    public static function avg(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'avg')));
    }

    public static function sum(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'sum')));
    }

    public static function count(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'count')));
    }

    public static function day(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'day')));
    }

    public static function month(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'month')));
    }

    public static function year(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'year')));
    }

    public static function int(string $name, ?int $length = null): MySQL
    {
        if (null === $length) {
            self::addQueryList([
                " {$name}",
                str_replace('(?)', '', self::getKey(Driver::MYSQL, 'int'))
            ]);
        } else {
            self::addQueryList([
                " {$name}",
                str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'int'))
            ]);
        }

        return new static;
    }

    public static function bigInt(string $name, ?int $length = null): MySQL
    {
        if (null === $length) {
            self::addQueryList([
                " {$name}",
                str_replace('(?)', '', self::getKey(Driver::MYSQL, 'bigint'))
            ]);
        } else {
            self::addQueryList([
                " {$name}",
                str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'bigint'))
            ]);
        }

        return new static;
    }

    public static function decimal(string $name): MySQL
    {
        self::addQueryList([
            " {$name}",
            self::getKey(Driver::MYSQL, 'decimal')
        ]);

        return new static;
    }

    public static function double(string $name): MySQL
    {
        self::addQueryList([
            " {$name}",
            self::getKey(Driver::MYSQL, 'double')
        ]);

        return new static;
    }

    public static function float(string $name): MySQL
    {
        self::addQueryList([
            " {$name}",
            self::getKey(Driver::MYSQL, 'float')
        ]);

        return new static;
    }

    public static function mediumInt(string $name, int $length): MySQL
    {
        self::addQueryList([
            " {$name}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'mediumint'))
        ]);

        return new static;
    }

    public static function real(string $name): MySQL
    {
        self::addQueryList([
            " {$name}",
            self::getKey(Driver::MYSQL, 'real')
        ]);

        return new static;
    }

    public static function smallInt(string $name, int $length): MySQL
    {
        self::addQueryList([
            " {$name}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'smallint'))
        ]);

        return new static;
    }

    public static function tinyInt(string $name, int $length): MySQL
    {
        self::addQueryList([
            " {$name}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'tinyint'))
        ]);

        return new static;
    }

    public static function blob(string $name): MySQL
    {
        self::addQueryList([
            " {$name}",
            self::getKey(Driver::MYSQL, 'blob')
        ]);

        return new static;
    }

    public static function varBinary(string $name, string|int $length = 'MAX'): MySQL
    {
        self::addQueryList([
            " {$name}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'varbinary'))
        ]);

        return new static;
    }

    public static function char(string $name, int $length): MySQL
    {
        self::addQueryList([
            " {$name}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'char'))
        ]);

        return new static;
    }

    public static function json(string $name): MySQL
    {
        self::addQueryList([
            " {$name}",
            self::getKey(Driver::MYSQL, 'json')
        ]);

        return new static;
    }

    public static function nchar(string $name, int $length): MySQL
    {
        self::addQueryList([
            " {$name}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'nchar'))
        ]);

        return new static;
    }

    public static function nvarchar(string $name, int $length): MySQL
    {
        self::addQueryList([
            " {$name}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'nvarchar'))
        ]);

        return new static;
    }

    public static function varchar(string $name, int $length): MySQL
    {
        self::addQueryList([
            " {$name}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'varchar'))
        ]);

        return new static;
    }

    public static function longText(string $name): MySQL
    {
        self::addQueryList([
            " {$name}",
            self::getKey(Driver::MYSQL, 'longtext')
        ]);

        return new static;
    }

    public static function mediumText(string $name): MySQL
    {
        self::addQueryList([
            " {$name}",
            self::getKey(Driver::MYSQL, 'mediumtext')
        ]);

        return new static;
    }

    public static function text(string $name, int $length): MySQL
    {
        self::addQueryList([
            " {$name}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'text'))
        ]);

        return new static;
    }

    public static function tinyText(string $name): MySQL
    {
        self::addQueryList([
            " {$name}",
            self::getKey(Driver::MYSQL, 'tinytext')
        ]);

        return new static;
    }

    public static function enum(string $name, array $options): MySQL
    {
        $split = array_map(fn ($op) => "'{$op}'", $options);

        self::addQueryList([
            " {$name}",
            str_replace('?', implode(', ', $split), self::getKey(Driver::MYSQL, 'enum'))
        ]);

        return new static;
    }

    public static function date(string $name): MySQL
    {
        self::addQueryList([
            " {$name}",
            self::getKey(Driver::MYSQL, 'date')
        ]);

        return new static;
    }

    public static function time(string $name): MySQL
    {
        self::addQueryList([
            " {$name}",
            self::getKey(Driver::MYSQL, 'time')
        ]);

        return new static;
    }

    public static function timeStamp(string $name): MySQL
    {
        self::addQueryList([
            " {$name}",
            self::getKey(Driver::MYSQL, 'timestamp')
        ]);

        return new static;
    }

    public static function dateTime(string $name): MySQL
    {
        self::addQueryList([
            " {$name}",
            self::getKey(Driver::MYSQL, 'datetime')
        ]);

        return new static;
    }
}
