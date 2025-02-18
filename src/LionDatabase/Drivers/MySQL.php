<?php

declare(strict_types=1);

namespace Lion\Database\Drivers;

use Closure;
use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Database\Interface\Drivers\DeleteInterface;
use Lion\Database\Interface\Drivers\InsertInterface;
use Lion\Database\Interface\Drivers\SelectInterface;
use Lion\Database\Interface\Drivers\TableInterface;
use Lion\Database\Interface\Drivers\UpdateInterface;
use Lion\Database\Interface\QueryInterface;
use Lion\Database\Interface\ReadDatabaseDataInterface;
use Lion\Database\Interface\RunDatabaseProcessesInterface;
use Lion\Database\Interface\SchemaDriverInterface;
use Lion\Database\Interface\TransactionInterface;
use Lion\Database\Traits\ConnectionInterfaceTrait;
use Lion\Database\Traits\Drivers\DeleteInterfaceTrait;
use Lion\Database\Traits\Drivers\InsertInterfaceTrait;
use Lion\Database\Traits\Drivers\SelectInterfaceTrait;
use Lion\Database\Traits\Drivers\TableInterfaceTrait;
use Lion\Database\Traits\Drivers\UpdateInterfaceTrait;
use Lion\Database\Traits\ExecuteInterfaceTrait;
use Lion\Database\Traits\GetAllInterfaceTrait;
use Lion\Database\Traits\GetInterfaceTrait;
use Lion\Database\Traits\QueryInterfaceTrait;
use Lion\Database\Traits\RunInterfaceTrait;
use Lion\Database\Traits\TransactionInterfaceTrait;
use PDO;

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
 * @property string $databaseMethod [Defines the database connection method to
 * use]
 *
 * @package Lion\Database\Drivers
 */
class MySQL extends Connection implements
    DatabaseConfigInterface,
    DeleteInterface,
    InsertInterface,
    QueryInterface,
    ReadDatabaseDataInterface,
    RunDatabaseProcessesInterface,
    SchemaDriverInterface,
    SelectInterface,
    TableInterface,
    TransactionInterface,
    UpdateInterface
{
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
    private static string $databaseMethod = Driver::MYSQL;

    /**
     * {@inheritdoc}
     */
    public static function isSchema(): MySQL
    {
        self::$isSchema = true;

        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public static function enableInsert(bool $enable = false): MySQL
    {
        self::$enableInsert = $enable;

        return new static();
    }

    /**
     * Nests the DATABASE statement in the current query
     *
     * @return MySQL
     */
    public static function database(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'database')]);

        return new static();
    }

    /**
     * Nests the TRUNCATE statement in the current query
     *
     * @return MySQL
     */
    public static function truncate(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'truncate')]);

        return new static();
    }

    /**
     * Nests the AUTO_INCREMENT statement in the current query
     *
     * @return MySQL
     */
    public static function autoIncrement(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'auto-increment')]);

        return new static();
    }

    /**
     * Nests the ACTION statement in the current query
     *
     * @return MySQL
     */
    public static function action(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'action')]);

        return new static();
    }

    /**
     * Nests the NO statement in the current query
     *
     * @return MySQL
     */
    public static function no(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'no')]);

        return new static();
    }

    /**
     * Nests the CASCADE statement in the current query
     *
     * @return MySQL
     */
    public static function cascade(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'cascade')]);

        return new static();
    }

    /**
     * Nests the RESTRICT statement in the current query
     *
     * @return MySQL
     */
    public static function restrict(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'restrict')]);

        return new static();
    }

    /**
     * Nests the ON DELETE statement in the current query
     *
     * @return MySQL
     */
    public static function onDelete(): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'on'),
            self::getKey(Driver::MYSQL, 'delete')
        ]);

        return new static();
    }

    /**
     * Nests the ON UPDATE statement in the current query
     *
     * @return MySQL
     */
    public static function onUpdate(): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'on'),
            self::getKey(Driver::MYSQL, 'update')
        ]);

        return new static();
    }

    /**
     * Nests the ON statement in the current query
     *
     * @return MySQL
     */
    public static function on(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'on')]);

        return new static();
    }

    /**
     * Nests the REFERENCES statement in the current query
     *
     * @return MySQL
     */
    public static function references(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'references')]);

        return new static();
    }

    /**
     * Nests the FOREIGN statement in the current query
     *
     * @return MySQL
     */
    public static function foreign(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'foreign')]);

        return new static();
    }

    /**
     * Nests the CONSTRAINT statement in the current query
     *
     * @return MySQL
     */
    public static function constraint(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'constraint')]);

        return new static();
    }

    /**
     * Nests the ADD statement in the current query
     *
     * @return MySQL
     */
    public static function add(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'add')]);

        return new static();
    }

    /**
     * Nests the ALTER statement in the current query
     *
     * @return MySQL
     */
    public static function alter(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'alter')]);

        return new static();
    }

    /**
     * Nests the COMMENT statement in the current query
     *
     * @param string $comment [Description comment]
     *
     * @return MySQL
     */
    public static function comment(string $comment = ''): MySQL
    {
        if ('' === $comment) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'comment')]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'comment'),
                ' ',
                "'{$comment}'"
            ]);
        }

        return new static();
    }

    /**
     * Nests the UNIQUE statement in the current query
     *
     * @return MySQL
     */
    public static function unique(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'unique')]);

        return new static();
    }

    /**
     * Nests the PRIMARY KEY statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function primaryKey(string $column): MySQL
    {
        self::addQueryList([
            str_replace('?', $column, self::getKey(Driver::MYSQL, 'primary-key'))
        ]);

        return new static();
    }

    /**
     * Nests the KEY statement in the current query
     *
     * @return MySQL
     */
    public static function key(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'key')]);

        return new static();
    }

    /**
     * Nests the PRIMARY statement in the current query
     *
     * @return MySQL
     */
    public static function primary(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'primary')]);

        return new static();
    }

    /**
     * Nests the ENGINE statement in the current query
     *
     * @param string $engine [Engine value]
     *
     * @return MySQL
     */
    public static function engine(string $engine = ''): MySQL
    {
        if ('' === $engine) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'engine')]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'engine'),
                ' = ',
                $engine
            ]);
        }

        return new static();
    }

    /**
     * Nests the NOT NULL statement in the current query
     *
     * @return MySQL
     */
    public static function notNull(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'not-null')]);

        return new static();
    }

    /**
     * Nests the NULL statement in the current query
     *
     * @return MySQL
     */
    public static function null(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'null')]);

        return new static();
    }

    /**
     * Nests the INNODB statement in the current query
     *
     * @return MySQL
     */
    public static function innoDB(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'innodb')]);

        return new static();
    }

    /**
     * Nests the COLLATE statement in the current query
     *
     * @param string $collate [Default collation for a comparison]
     *
     * @return MySQL
     */
    public static function collate(string $collate = ''): MySQL
    {
        if ('' === $collate) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'collate')]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'collate'),
                ' = ',
                $collate
            ]);
        }

        return new static();
    }

    /**
     * Nests the SET statement in the current query
     *
     * @param string $set [Column values that consist of multiple set members]
     *
     * @return MySQL
     */
    public static function set(string $set = ''): MySQL
    {
        if ('' === $set) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'set')]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'set'),
                ' = ',
                $set
            ]);
        }

        return new static();
    }

    /**
     * Nests the CHARACTER statement in the current query
     *
     * @return MySQL
     */
    public static function character(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'character')]);

        return new static();
    }

    /**
     * Nests the DEFAULT statement in the current query
     *
     * @param int|string $default [Default value]
     *
     * @return MySQL
     */
    public static function default(int|string $default = ''): MySQL
    {
        if ('' === $default) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'default')]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'default'),
                ' ',
                (is_string($default) ? "'{$default}'" : $default)
            ]);
        }

        return new static();
    }

    /**
     * Nests the SCHEMA statement in the current query
     *
     * @return MySQL
     */
    public static function schema(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'schema')]);

        return new static();
    }

    /**
     * Nests the query in the current query
     *
     * @param string $query [Query SQL]
     *
     * @return MySQL
     */
    public static function addQuery(string $query): MySQL
    {
        self::addQueryList([" {$query}"]);

        return new static();
    }

    /**
     * Nests the IF EXISTS statement in the current query
     *
     * @param string $ifExist [Value]
     *
     * @return MySQL
     */
    public static function ifExists(string $ifExist): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'exists'),
            " `{$ifExist}`"
        ]);

        return new static();
    }

    /**
     * Nests the IF NOT EXISTS statement in the current query
     *
     * @param string $ifNotExist [Value]
     *
     * @return MySQL
     */
    public static function ifNotExists(string $ifNotExist): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'not'),
            self::getKey(Driver::MYSQL, 'exists'),
            " `{$ifNotExist}`"
        ]);

        return new static();
    }

    /**
     * Nests the USE statement in the current query
     *
     * @param string $use [Use value]
     *
     * @return MySQL
     */
    public static function use(string $use): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `{$use}`"
        ]);

        return new static();
    }

    /**
     * Nests the BEGIN statement in the current query
     *
     * @return MySQL
     */
    public static function begin(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'begin')]);

        return new static();
    }

    /**
     * Nests the END statement in the current query
     *
     * @return MySQL
     */
    public static function end(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'end')]);

        return new static();
    }

    /**
     * Nests the CREATE statement in the current query
     *
     * @return MySQL
     */
    public static function create(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'create')]);

        return new static();
    }

    /**
     * Nests the PROCEDURE statement in the current query
     *
     * @return MySQL
     */
    public static function procedure(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'procedure')]);

        return new static();
    }

    /**
     * Nests the STATUS statement in the current query
     *
     * @return MySQL
     */
    public static function status(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'status')]);

        return new static();
    }

    /**
     * Add semicolon to the end of the query
     *
     * @param string $close
     *
     * @return MySQL
     */
    public static function closeQuery(string $close = ';'): MySQL
    {
        self::addQueryList([$close]);

        return new static();
    }

    /**
     * Nests the FULL statement in the current query
     *
     * @return MySQL
     */
    public static function full(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'full')]);

        return new static();
    }

    /**
     * Adds a SQL statement to the current query
     *
     * @param Closure $callback [SQL query group]
     *
     * @return MySQL
     */
    public static function groupQuery(Closure $callback): MySQL
    {
        self::openGroup();

        $callback();

        self::closeGroup();

        return new static();
    }

    /**
     * Nests the RECURSIVE AS statement in the current query
     *
     * @param string $name [Reference name]
     *
     * @return MySQL
     */
    public static function recursive(string $name): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'recursive'),
            " {$name}",
            self::getKey(Driver::MYSQL, 'as')
        ]);

        return new static();
    }

    /**
     * Nests the WITH statement in the current query
     *
     * @param bool $isString [Determines whether to nest the current query or
     * return the WITH statement]
     *
     * @return MySQL|string
     */
    public static function with(bool $isString = false): MySQL|string
    {
        if ($isString) {
            return self::getKey(Driver::MYSQL, 'with');
        }

        self::addQueryList([self::getKey(Driver::MYSQL, 'with')]);

        return new static();
    }

    /**
     * Nests the VIEW statement in the current query
     *
     * @param string|bool $view [Nests the view in the current query or nests
     * the VIEW statement in the current query]
     * @param bool $withDatabase [Determines whether to nest the current
     * database in the view]
     *
     * @return MySQL
     */
    public static function view(string|bool $view = true, bool $withDatabase = true): MySQL
    {
        if (is_string($view)) {
            self::$view = !$withDatabase ? $view : self::$dbname . ".{$view}";
        } else {
            if ($view) {
                self::addQueryList([self::getKey(Driver::MYSQL, 'view')]);
            }
        }

        return new static();
    }

    /**
     * Nests the IS NULL statement in the current query
     *
     * @return MySQL
     */
    public static function isNull(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'is-null')]);

        return new static();
    }

    /**
     * Nests the IS NOT NULL statement in the current query
     *
     * @return MySQL
     */
    public static function isNotNull(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'is-not-null')]);

        return new static();
    }

    /**
     * Nests the OFFSET statement in the current query
     *
     * @param int $offset [Must be greater than or equal to zero]
     *
     * @return MySQL
     */
    public static function offset(int $offset = 0): MySQL
    {
        self::addRows([$offset]);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'offset'),
            ' ?'
        ]);

        return new static();
    }

    /**
     * Nests the UNION ALL statement in the current query
     *
     * @return MySQL
     */
    public static function unionAll(): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'union'),
            self::getKey(Driver::MYSQL, 'all')
        ]);

        return new static();
    }

    /**
     * Nests the UNION statement in the current query
     *
     * @return MySQL
     */
    public static function union(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'union')]);

        return new static();
    }

    /**
     * Nests the AS statement in the current query
     *
     * @param string $column
     * @param string $as
     *
     * @return string
     */
    public static function as(string $column, string $as): string
    {
        return $column . self::getKey(Driver::MYSQL, 'as') . " {$as}";
    }

    /**
     * Nests the CONCAT statement in the current query
     *
     * @return array<int, string>|string|null
     */
    public static function concat(): array|string|null
    {
        return str_replace('*', implode(', ', func_get_args()), self::getKey(Driver::MYSQL, 'concat'));
    }

    /**
     * Nests the CREATE TABLE statement in the current query
     *
     * @return MySQL
     */
    public static function createTable(): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'create'),
            self::getKey(Driver::MYSQL, 'table'),
            ' ',
            self::$table
        ]);

        return new static();
    }

    /**
     * Nests the SHOW statement in the current query
     *
     * @return MySQL
     */
    public static function show(): MySQL
    {
        if (empty(self::$actualCode)) {
            self::$actualCode = uniqid('code-');
        }

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;

        self::$sql = self::getKey(Driver::MYSQL, 'show');

        return new static();
    }

    /**
     * Add a FROM to the current statement
     *
     * @param string|null $from [Reference table or function]
     *
     * @return MySQL
     */
    public static function from(?string $from = null): MySQL
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

        return new static();
    }

    /**
     * Nests the INDEX statement in the current query
     *
     * @return MySQL
     */
    public static function index(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'index')]);

        return new static();
    }

    /**
     * Nests the DROP statement in the current query
     *
     * @return MySQL
     */
    public static function drop(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'drop')]);

        return new static();
    }

    /**
     * Nests the 'SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME,
     * REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM
     * information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=? AND TABLE_NAME=?
     * AND REFERENCED_COLUMN_NAME IS NOT NULL' statement in the current query
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Nests the TABLES statement in the current query
     *
     * @return MySQL
     */
    public static function tables(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'tables')]);

        return new static();
    }

    /**
     * Nests the COLUMNS statement in the current query
     *
     * @return MySQL
     */
    public static function columns(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'columns')]);

        return new static();
    }

    /**
     * Nesting multiple values in an insert run
     *
     * @param array<int, string> $columns [List of columns]
     * @param array<int, array<string, mixed>> $rows [Insertion rows]
     *
     * @return MySQL
     */
    public static function bulk(array $columns, array $rows): MySQL
    {
        if (empty(self::$actualCode)) {
            self::$actualCode = uniqid('code-');
        }

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

        return new static();
    }

    /**
     * Nests the IN statement in the current query
     *
     * @param array|null $values [List of values]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Nests the CALL statement in the current query
     *
     * @param string $storedProcedure [Stored Procedure Name]
     * @param array<string, mixed> $rows [List of values]
     *
     * @return MySQL
     */
    public static function call(string $storedProcedure, array $rows = []): MySQL
    {
        if (empty(self::$actualCode)) {
            self::$actualCode = uniqid('code-');
        }

        self::addRows($rows);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'call'),
            ' ',
            self::$dbname,
            ".{$storedProcedure}(",
            self::addCharacter($rows),
            ")"
        ]);

        return new static();
    }

    /**
     * Nests the HAVING statement in the current query
     *
     * @return MySQL
     */
    public static function having(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'having')]);

        return new static();
    }

    /**
     * Nests the SELECT DISTINCT statement in the current query
     *
     * @return MySQL
     */
    public static function selectDistinct(): MySQL
    {
        if (empty(self::$actualCode)) {
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

        return new static();
    }

    /**
     * Nests the BETWEEN statement in the current query
     *
     * @param mixed $between [Value between]
     * @param mixed $and [Value and]
     *
     * @return MySQL
     */
    public static function between(mixed $between, mixed $and): MySQL
    {
        self::addRows([$between, $and]);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'between'),
            ' ?',
            self::getKey(Driver::MYSQL, 'and'),
            ' ? '
        ]);

        return new static();
    }

    /**
     * Nests the LIKE statement in the current query
     *
     * @param string $like [Preference value]
     *
     * @return MySQL
     */
    public static function like(string $like): MySQL
    {
        self::addRows([$like]);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'like'),
            ' ',
            self::addCharacter([$like])
        ]);

        return new static();
    }

    /**
     * Nests the GROUP BY statement in the current query
     *
     * @return MySQL
     */
    public static function groupBy(): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'group-by'),
            ' ',
            self::addColumns(func_get_args())
        ]);

        return new static();
    }

    /**
     * Nests the LIMIT statement in the current query
     *
     * @param int $start [Start limit]
     * @param int|null $limit [End limit]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Nests the ASC statement in the current query
     *
     * @param bool $isString [Determines whether to nest the ASC statement in
     * the current query or return the statement]
     *
     * @return MySQL|string
     */
    public static function asc(bool $isString = false): MySQL|string
    {
        if ($isString) {
            return self::getKey(Driver::MYSQL, 'asc');
        }

        self::addQueryList([self::getKey(Driver::MYSQL, 'asc')]);

        return new static();
    }

    /**
     * Nests the DESC statement in the current query
     *
     * @param bool $isString [Determines whether to nest the DESC statement in
     * the current query or return the statement]
     *
     * @return MySQL|string
     */
    public static function desc(bool $isString = false): MySQL|string
    {
        if ($isString) {
            return self::getKey(Driver::MYSQL, 'desc');
        }

        self::addQueryList([self::getKey(Driver::MYSQL, 'desc')]);

        return new static();
    }

    /**
     * Nests the ORDER BY statement in the current query
     *
     * @return MySQL
     */
    public static function orderBy(): MySQL
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'order-by'),
            ' ',
            self::addColumns(func_get_args())
        ]);

        return new static();
    }

    /**
     * Nests the INNER statement in the current query
     *
     * @return MySQL
     */
    public static function inner(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'inner')]);

        return new static();
    }

    /**
     * Nests the LEFT statement in the current query
     *
     * @return MySQL
     */
    public static function left(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'left')]);

        return new static();
    }

    /**
     * Nests the RIGHT statement in the current query
     *
     * @return MySQL
     */
    public static function right(): MySQL
    {
        self::addQueryList([self::getKey(Driver::MYSQL, 'right')]);

        return new static();
    }

    /**
     * Nests the JOIN statement in the current query
     *
     * @param string $table [Table name]
     * @param string $valueFrom [Column from]
     * @param string $valueTo [Column to]
     * @param bool $withAlias [Determines if the table is nested with the
     * database name]
     *
     * @return MySQL
     */
    public static function join(string $table, string $valueFrom, string $valueTo, bool $withAlias = true): MySQL
    {
        if ($withAlias) {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'join'),
                ' ',
                self::$dbname,
                ".{$table}",
                self::getKey(Driver::MYSQL, 'on'),
                " {$valueFrom} = {$valueTo}"
            ]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'join'),
                " {$table}",
                self::getKey(Driver::MYSQL, 'on'),
                " {$valueFrom} = {$valueTo}"
            ]);
        }

        return new static();
    }

    /**
     * Nests the WHERE statement in the current query
     *
     * @param Closure|string|bool $where [You can add a WHERE to the current
     * statement, group by group, or return the WHERE statement]
     *
     * @return MySQL
     */
    public static function where(Closure|string|bool $where = true): MySQL
    {
        if (is_callable($where)) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'where')]);

            $where();
        } elseif (is_string($where)) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'where'), " {$where}"]);
        } else {
            if ($where) {
                self::addQueryList([self::getKey(Driver::MYSQL, 'where')]);
            }
        }

        return new static();
    }

    /**
     * Nests the AND statement in the current query
     *
     * @param Closure|string|bool $and [You can add a AND to the current
     * statement, group by group, or return the AND statement]
     *
     * @return MySQL
     */
    public static function and(Closure|string|bool $and = true): MySQL
    {
        if (is_callable($and)) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'and')]);

            $and();
        } elseif (is_string($and)) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'and'), " {$and}"]);
        } else {
            if ($and) {
                self::addQueryList([self::getKey(Driver::MYSQL, 'and')]);
            }
        }

        return new static();
    }

    /**
     * Nests the OR statement in the current query
     *
     * @param Closure|string|bool $or [You can add a OR to the current
     * statement, group by group, or return the OR statement]
     *
     * @return MySQL
     */
    public static function or(Closure|string|bool $or = true): MySQL
    {
        if (is_callable($or)) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'or')]);

            $or();
        } elseif (is_string($or)) {
            self::addQueryList([self::getKey(Driver::MYSQL, 'or'), " {$or}"]);
        } else {
            if ($or) {
                self::addQueryList([self::getKey(Driver::MYSQL, 'or')]);
            }
        }

        return new static();
    }

    /**
     * Gets the column
     *
     * @param string $column [Column name]
     * @param string $table [Table name]
     *
     * @return string
     */
    public static function getColumn(string $column, string $table = ''): string
    {
        return '' === $table ? trim($column) : trim("{$table}.{$column}");
    }

    /**
     * Adds a column to the current statement
     *
     * @param string $column [Column name]
     * @param string $table [Table name]
     *
     * @return MySQL
     */
    public static function column(string $column, string $table = ''): MySQL
    {
        self::addQueryList('' === $table ? [' ', trim($column)] : [' ', trim("{$table}.{$column}")]);

        return new static();
    }

    /**
     * Adds an "equals to" to the current statement
     *
     * @param string $column [Column name]
     * @param mixed $equalTo [Equal to]
     *
     * @return MySQL
     */
    public static function equalTo(string $column, mixed $equalTo): MySQL
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([' ', trim($column), ' = ', trim($equalTo)]);
        } else {
            self::addRows([$equalTo]);

            self::addQueryList([' ', trim($column . ' = ?')]);
        }

        return new static();
    }

    /**
     * Adds a "not equal to" to the current statement
     *
     * @param string $column [Column name]
     * @param mixed $notEqualTo [Not equal to]
     *
     * @return MySQL
     */
    public static function notEqualTo(string $column, mixed $notEqualTo): MySQL
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([' ', trim($column), ' <> ', trim($notEqualTo)]);
        } else {
            self::addRows([$notEqualTo]);

            self::addQueryList([' ', trim($column . ' <> ?')]);
        }

        return new static();
    }

    /**
     * Adds a "greater than" to the current statement
     *
     * @param string $column [Column name]
     * @param mixed $greaterThan [Greather than]
     *
     * @return MySQL
     */
    public static function greaterThan(string $column, mixed $greaterThan): MySQL
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([' ', trim($column), ' > ', trim($greaterThan)]);
        } else {
            self::addRows([$greaterThan]);

            self::addQueryList([' ', trim($column . ' > ?')]);
        }

        return new static();
    }

    /**
     * Adds a "less than" to the current statement
     *
     * @param string $column [Column name]
     * @param mixed $lessThan [Less than]
     *
     * @return MySQL
     */
    public static function lessThan(string $column, mixed $lessThan): MySQL
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([' ', trim($column), ' < ', trim($lessThan)]);
        } else {
            self::addRows([$lessThan]);

            self::addQueryList([' ', trim($column . ' < ?')]);
        }

        return new static();
    }

    /**
     * Adds a "greater than or equal to" to the current statement
     *
     * @param string $column [Column name]
     * @param mixed $greaterThanOrEqualTo [Greater than or equal to]
     *
     * @return MySQL
     */
    public static function greaterThanOrEqualTo(string $column, mixed $greaterThanOrEqualTo): MySQL
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([' ', trim($column), ' >= ', trim($greaterThanOrEqualTo)]);
        } else {
            self::addRows([$greaterThanOrEqualTo]);

            self::addQueryList([' ', trim($column . ' >= ?')]);
        }

        return new static();
    }

    /**
     * Adds a "less than or equal to" to the current statement
     *
     * @param string $column [Column name]
     * @param mixed $lessThanOrEqualTo [Less than or equal to]
     *
     * @return MySQL
     */
    public static function lessThanOrEqualTo(string $column, mixed $lessThanOrEqualTo): MySQL
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([' ', trim($column), ' <= ', trim($lessThanOrEqualTo)]);
        } else {
            self::addRows([$lessThanOrEqualTo]);

            self::addQueryList([' ', trim($column . ' <= ?')]);
        }

        return new static();
    }

    /**
     * Nests the MIN statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return string
     */
    public static function min(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'min')));
    }

    /**
     * Nests the MAX statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return string
     */
    public static function max(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'max')));
    }

    /**
     * Nests the AVG statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return string
     */
    public static function avg(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'avg')));
    }

    /**
     * Nests the SUM statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return string
     */
    public static function sum(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'sum')));
    }

    /**
     * Nests the COUNT statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return string
     */
    public static function count(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'count')));
    }

    /**
     * Nests the DAY statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return string
     */
    public static function day(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'day')));
    }

    /**
     * Nests the MONTH statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return string
     */
    public static function month(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'month')));
    }

    /**
     * Nests the YEAR statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return string
     */
    public static function year(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey(Driver::MYSQL, 'year')));
    }

    /**
     * Nests the INT statement in the current query
     *
     * @param string $column [Column name]
     * @param int|null $length [Length]
     *
     * @return MySQL
     */
    public static function int(string $column, ?int $length = null): MySQL
    {
        if (null === $length) {
            self::addQueryList([
                " {$column}",
                str_replace('(?)', '', self::getKey(Driver::MYSQL, 'int'))
            ]);
        } else {
            self::addQueryList([
                " {$column}",
                str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'int'))
            ]);
        }

        return new static();
    }

    /**
     * Nests the BIGINT statement in the current query
     *
     * @param string $column [Column name]
     * @param int|null $length [Length]
     *
     * @return MySQL
     */
    public static function bigInt(string $column, ?int $length = null): MySQL
    {
        if (null === $length) {
            self::addQueryList([
                " {$column}",
                str_replace('(?)', '', self::getKey(Driver::MYSQL, 'bigint'))
            ]);
        } else {
            self::addQueryList([
                " {$column}",
                str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'bigint'))
            ]);
        }

        return new static();
    }

    /**
     * Nests the DECIMAL statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function decimal(string $column): MySQL
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'decimal')
        ]);

        return new static();
    }

    /**
     * Nests the DOUBLE statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function double(string $column): MySQL
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'double')
        ]);

        return new static();
    }

    /**
     * Nests the FLOAT statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function float(string $column): MySQL
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'float')
        ]);

        return new static();
    }

    /**
     * Nests the MEDIUMINT statement in the current query
     *
     * @param string $column [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
    public static function mediumInt(string $column, int $length): MySQL
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'mediumint'))
        ]);

        return new static();
    }

    /**
     * Nests the REAL statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function real(string $column): MySQL
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'real')
        ]);

        return new static();
    }

    /**
     * Nests the SMALLINT statement in the current query
     *
     * @param string $column [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
    public static function smallInt(string $column, int $length): MySQL
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'smallint'))
        ]);

        return new static();
    }

    /**
     * Nests the TINYINT statement in the current query
     *
     * @param string $column [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
    public static function tinyInt(string $column, int $length): MySQL
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'tinyint'))
        ]);

        return new static();
    }

    /**
     * Nests the BLOB statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function blob(string $column): MySQL
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'blob')
        ]);

        return new static();
    }

    /**
     * Nests the VARBINARY statement in the current query
     *
     * @param string $column [Column name]
     * @param string|int $length [length]
     *
     * @return MySQL
     */
    public static function varBinary(string $column, string|int $length = 'MAX'): MySQL
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'varbinary'))
        ]);

        return new static();
    }

    /**
     * Nests the CHAR statement in the current query
     *
     * @param string $column [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
    public static function char(string $column, int $length): MySQL
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'char'))
        ]);

        return new static();
    }

    /**
     * Nests the JSON statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function json(string $column): MySQL
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'json')
        ]);

        return new static();
    }

    /**
     * Nests the NCHAR statement in the current query
     *
     * @param string $column [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
    public static function nchar(string $column, int $length): MySQL
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'nchar'))
        ]);

        return new static();
    }

    /**
     * Nests the NVARCHAR statement in the current query
     *
     * @param string $column [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
    public static function nvarchar(string $column, int $length): MySQL
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'nvarchar'))
        ]);

        return new static();
    }

    /**
     * Nests the VARCHAR statement in the current query
     *
     * @param string $column [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
    public static function varchar(string $column, int $length): MySQL
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'varchar'))
        ]);

        return new static();
    }

    /**
     * Nests the LONGTEXT statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function longText(string $column): MySQL
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'longtext')
        ]);

        return new static();
    }

    /**
     * Nests the MEDIUMTEXT statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function mediumText(string $column): MySQL
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'mediumtext')
        ]);

        return new static();
    }

    /**
     * Nests the TEXT statement in the current query
     *
     * @param string $column [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
    public static function text(string $column, int $length): MySQL
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'text'))
        ]);

        return new static();
    }

    /**
     * Nests the TINYTEXT statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function tinyText(string $column): MySQL
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'tinytext')
        ]);

        return new static();
    }

    /**
     * Nests the ENUM statement in the current query
     *
     * @param string $column [Column name]
     * @param array<int, string> $options [Options]
     *
     * @return MySQL
     */
    public static function enum(string $column, array $options): MySQL
    {
        $split = array_map(fn ($op) => "'{$op}'", $options);

        self::addQueryList([
            " {$column}",
            str_replace('?', implode(', ', $split), self::getKey(Driver::MYSQL, 'enum'))
        ]);

        return new static();
    }

    /**
     * Nests the DATE statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function date(string $column): MySQL
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'date')
        ]);

        return new static();
    }

    /**
     * Nests the TIME statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function time(string $column): MySQL
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'time')
        ]);

        return new static();
    }

    /**
     * Nests the TIMESTAMP statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function timeStamp(string $column): MySQL
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'timestamp')
        ]);

        return new static();
    }

    /**
     * Nests the DATETIME statement in the current query
     *
     * @param string $column [Column name]
     *
     * @return MySQL
     */
    public static function dateTime(string $column): MySQL
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'datetime')
        ]);

        return new static();
    }
}
