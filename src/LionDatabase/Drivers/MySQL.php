<?php

declare(strict_types=1);

namespace Lion\Database\Drivers;

use Closure;
use Lion\Database\Connection;
use Lion\Database\Driver;
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
use PDO;

/**
 * Provides an interface to build SQL queries dynamically in PHP applications
 * that interact with MySQL databases.
 *
 * Key Features:
 *
 * * Intuitive methods: Simple methods to build SQL queries programmatically.
 * * SQL Injection Prevention: Helps prevent SQL injection attacks by sanitizing
 * data entered in queries.
 * * Flexibility: Allows the construction of dynamic queries adapted to
 * different application scenarios.
 * * Optimization for MySQL: Designed specifically to work with MySQL,
 * guaranteeing compatibility and optimization with this DBMS.
 */
class MySQL extends Connection implements
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
    use GetInterfaceTrait;
    use GetQueryStringInterfaceTrait;
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

    /**
     * Nests the SELECT EXISTS statement in the current query.
     *
     * @param Closure $callable Nest the query in the EXISTS statement.
     *
     * @return self
     */
    public function selectExists(Closure $callable): self
    {
        if (empty(self::$actualCode)) {
            self::$actualCode = uniqid('code-');
        }

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'select-exists'),
        ]);

        self::groupQuery($callable);

        return new self();
    }

    /**
     * Nests the TRUNCATE statement in the current query
     *
     * @return self
     */
    public static function truncate(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'truncate'),
        ]);

        return new self();
    }

    /**
     * Nests the AUTO_INCREMENT statement in the current query
     *
     * @return self
     */
    public static function autoIncrement(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'auto-increment'),
        ]);

        return new self();
    }

    /**
     * Nests the ACTION statement in the current query
     *
     * @return self
     */
    public static function action(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'action'),
        ]);

        return new self();
    }

    /**
     * Nests the NO statement in the current query
     *
     * @return self
     */
    public static function no(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'no'),
        ]);

        return new self();
    }

    /**
     * Nests the CASCADE statement in the current query
     *
     * @return self
     */
    public static function cascade(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'cascade'),
        ]);

        return new self();
    }

    /**
     * Nests the RESTRICT statement in the current query
     *
     * @return self
     */
    public static function restrict(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'restrict'),
        ]);

        return new self();
    }

    /**
     * Nests the ON DELETE statement in the current query
     *
     * @return self
     */
    public static function onDelete(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'on'),
            self::getKey(Driver::MYSQL, 'delete'),
        ]);

        return new self();
    }

    /**
     * Nests the ON statement in the current query
     *
     * @return self
     */
    public static function on(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'on'),
        ]);

        return new self();
    }

    /**
     * Nests the REFERENCES statement in the current query
     *
     * @return self
     */
    public static function references(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'references'),
        ]);

        return new self();
    }

    /**
     * Nests the FOREIGN statement in the current query
     *
     * @return self
     */
    public static function foreign(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'foreign'),
        ]);

        return new self();
    }

    /**
     * Nests the CONSTRAINT statement in the current query
     *
     * @return self
     */
    public static function constraint(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'constraint'),
        ]);

        return new self();
    }

    /**
     * Nests the ADD statement in the current query
     *
     * @return self
     */
    public static function add(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'add'),
        ]);

        return new self();
    }

    /**
     * Nests the ALTER statement in the current query
     *
     * @return self
     */
    public static function alter(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'alter'),
        ]);

        return new self();
    }

    /**
     * Nests the COMMENT statement in the current query
     *
     * @param string $comment Description comment
     *
     * @return self
     */
    public static function comment(string $comment = ''): self
    {
        if ('' === $comment) {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'comment'),
            ]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'comment'),
                ' ',
                "'{$comment}'"
            ]);
        }

        return new self();
    }

    /**
     * Nests the UNIQUE statement in the current query
     *
     * @return self
     */
    public static function unique(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'unique'),
        ]);

        return new self();
    }

    /**
     * Nests the PRIMARY KEY statement in the current query
     *
     * @param string|null $column Column name
     *
     * @return self
     */
    public static function primaryKey(?string $column = null): self
    {
        self::addQueryList([
            str_replace($column ? '?' : '(?)', $column ?? '', self::getKey(Driver::MYSQL, 'primary-key')),
        ]);

        return new self();
    }

    /**
     * Nests the KEY statement in the current query
     *
     * @return self
     */
    public static function key(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'key'),
        ]);

        return new self();
    }

    /**
     * Nests the PRIMARY statement in the current query
     *
     * @return self
     */
    public static function primary(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'primary'),
        ]);

        return new self();
    }

    /**
     * Nests the ENGINE statement in the current query
     *
     * @param string $engine Engine value
     *
     * @return self
     */
    public static function engine(string $engine = ''): self
    {
        if ('' === $engine) {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'engine'),
            ]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'engine'),
                ' = ',
                $engine,
            ]);
        }

        return new self();
    }

    /**
     * Nests the NOT NULL statement in the current query
     *
     * @return self
     */
    public static function notNull(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'not-null'),
        ]);

        return new self();
    }

    /**
     * Nests the NULL statement in the current query
     *
     * @return self
     */
    public static function null(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'null'),

        ]);

        return new self();
    }

    /**
     * Nests the INNODB statement in the current query
     *
     * @return self
     */
    public static function innoDB(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'innodb'),
        ]);

        return new self();
    }

    /**
     * Nests the COLLATE statement in the current query
     *
     * @param string $collate Default collation for a comparison
     *
     * @return self
     */
    public static function collate(string $collate = ''): self
    {
        if ('' === $collate) {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'collate'),
            ]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'collate'),
                ' = ',
                $collate,
            ]);
        }

        return new self();
    }

    /**
     * Nests the SET statement in the current query
     *
     * @param string $set [Column values that consist of multiple set members]
     *
     * @return self
     */
    public static function set(string $set = ''): self
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

        return new self();
    }

    /**
     * Nests the CHARACTER statement in the current query
     *
     * @return self
     */
    public static function character(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'character'),
        ]);

        return new self();
    }

    /**
     * Nests the DEFAULT statement in the current query
     *
     * @param int|string $default Default value
     *
     * @return self
     */
    public static function default(int|string $default = ''): self
    {
        if ('' === $default) {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'default'),
            ]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'default'),
                ' ',
                (is_string($default) ? "'{$default}'" : $default),
            ]);
        }

        return new self();
    }

    /**
     * Nests the SCHEMA statement in the current query
     *
     * @return self
     */
    public static function schema(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'schema'),
        ]);

        return new self();
    }

    /**
     * Nests the query in the current query
     *
     * @param string $query Query SQL
     *
     * @return self
     */
    public static function addQuery(string $query): self
    {
        self::addQueryList([
            " {$query}",
        ]);

        return new self();
    }

    /**
     * Nests the IF EXISTS statement in the current query
     *
     * @param string $ifExist Value
     *
     * @return self
     */
    public static function ifExists(string $ifExist): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'exists'),
            " `{$ifExist}`",
        ]);

        return new self();
    }

    /**
     * Nests the IF NOT EXISTS statement in the current query
     *
     * @param string $ifNotExist Value
     *
     * @return self
     */
    public static function ifNotExists(string $ifNotExist): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'not'),
            self::getKey(Driver::MYSQL, 'exists'),
            " `{$ifNotExist}`",
        ]);

        return new self();
    }

    /**
     * Nests the USE statement in the current query
     *
     * @param string $use Use value
     *
     * @return self
     */
    public static function use(string $use): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `{$use}`",
        ]);

        return new self();
    }

    /**
     * Nests the BEGIN statement in the current query
     *
     * @return self
     */
    public static function begin(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'begin'),
        ]);

        return new self();
    }

    /**
     * Nests the END statement in the current query
     *
     * @return self
     */
    public static function end(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'end'),
        ]);

        return new self();
    }

    /**
     * Nests the CREATE statement in the current query
     *
     * @return self
     */
    public static function create(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'create'),
        ]);

        return new self();
    }

    /**
     * Nests the PROCEDURE statement in the current query
     *
     * @return self
     */
    public static function procedure(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'procedure'),
        ]);

        return new self();
    }

    /**
     * Nests the STATUS statement in the current query
     *
     * @return self
     */
    public static function status(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'status'),
        ]);

        return new self();
    }

    /**
     * Add semicolon to the end of the query
     *
     * @param string $close
     *
     * @return self
     */
    public static function closeQuery(string $close = ';'): self
    {
        self::addQueryList([
            $close,
        ]);

        return new self();
    }

    /**
     * Nests the FULL statement in the current query
     *
     * @return self
     */
    public static function full(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'full'),
        ]);

        return new self();
    }

    /**
     * Adds a SQL statement to the current query
     *
     * @param Closure $callback SQL query group
     *
     * @return self
     */
    public static function groupQuery(Closure $callback): self
    {
        self::openGroup();

        $callback();

        self::closeGroup();

        return new self();
    }

    /**
     * Nests the RECURSIVE AS statement in the current query
     *
     * @param string $name Reference name
     *
     * @return self
     */
    public static function recursive(string $name): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'recursive'),
            " {$name}",
            self::getKey(Driver::MYSQL, 'as'),
        ]);

        return new self();
    }

    /**
     * Nests the WITH statement in the current query
     *
     * @param bool $isString Determines whether to nest the current query or return
     * the WITH statement
     *
     * @return self|string
     */
    public static function with(bool $isString = false): self|string
    {
        if ($isString) {
            return self::getKey(Driver::MYSQL, 'with');
        }

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'with'),
        ]);

        return new self();
    }

    /**
     * Nests the VIEW statement in the current query
     *
     * @param string|bool $view Nests the view in the current query or nests the
     * VIEW statement in the current query
     * @param bool $withDatabase Determines whether to nest the current database in
     * the view
     *
     * @return self
     */
    public static function view(string|bool $view = true, bool $withDatabase = false): self
    {
        if (is_string($view)) {
            self::$view = !$withDatabase ? $view : self::$dbname . ".{$view}";
        } else {
            if ($view) {
                self::addQueryList([
                    self::getKey(Driver::MYSQL, 'view'),
                ]);
            }
        }

        return new self();
    }

    /**
     * Nests the IS NULL statement in the current query
     *
     * @return self
     */
    public static function isNull(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'is-null'),
        ]);

        return new self();
    }

    /**
     * Nests the IS NOT NULL statement in the current query
     *
     * @return self
     */
    public static function isNotNull(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'is-not-null'),
        ]);

        return new self();
    }

    /**
     * Nests the OFFSET statement in the current query
     *
     * @param int $offset Must be greater than or equal to zero
     *
     * @return self
     */
    public static function offset(int $offset = 0): self
    {
        self::addRows([
            $offset,
        ]);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'offset'),
            ' ?',
        ]);

        return new self();
    }

    /**
     * Nests the UNION ALL statement in the current query
     *
     * @return self
     */
    public static function unionAll(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'union'),
            self::getKey(Driver::MYSQL, 'all'),
        ]);

        return new self();
    }

    /**
     * Nests the UNION statement in the current query
     *
     * @return self
     */
    public static function union(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'union'),
        ]);

        return new self();
    }

    /**
     * Nests the AS statement in the current query
     *
     * @param string $as Alias name
     * @param string|null $column Column name
     * @param bool $isString Determines whether to get the aliased string or assign
     * the AS statement to the current query
     *
     * @return self|string
     */
    public static function as(string $as, ?string $column = null, bool $isString = false): self|string
    {
        if ($isString) {
            return $column . self::getKey(Driver::MYSQL, 'as') . " {$as}";
        }

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'as'),
            " {$as}",
        ]);

        return new self();
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
     * @return self
     */
    public static function createTable(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'create'),
            self::getKey(Driver::MYSQL, 'table'),
            ' ',
            self::$table,
        ]);

        return new self();
    }

    /**
     * Nests the SHOW statement in the current query
     *
     * @return self
     */
    public static function show(): self
    {
        if (empty(self::$actualCode)) {
            self::$actualCode = uniqid('code-');
        }

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;

        self::$sql = self::getKey(Driver::MYSQL, 'show');

        return new self();
    }

    /**
     * Add a FROM to the current statement
     *
     * @param string|null $from [Reference table or function]
     *
     * @return self
     */
    public static function from(?string $from = null): self
    {
        if (null === $from) {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'from'),
                ' ',
                ('' === trim(self::$table) ? self::$view : self::$table)
            ]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'from'), ' ',
                $from,
            ]);
        }

        return new self();
    }

    /**
     * Nests the INDEX statement in the current query
     *
     * @return self
     */
    public static function index(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'index'),
        ]);

        return new self();
    }

    /**
     * Nests the DROP statement in the current query
     *
     * @return self
     */
    public static function drop(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'drop'),
        ]);

        return new self();
    }

    /**
     * Nests the 'SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME,
     * REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM
     * information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=? AND TABLE_NAME=?
     * AND REFERENCED_COLUMN_NAME IS NOT NULL' statement in the current query
     *
     * @return self
     */
    public static function constraints(): self
    {
        self::addRows(explode('.', self::$table));

        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'select'),
            ' CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME',
            self::getKey(Driver::MYSQL, 'from'),
            ' information_schema.KEY_COLUMN_USAGE WHERE ',
            'TABLE_SCHEMA=? AND TABLE_NAME=? AND REFERENCED_COLUMN_NAME IS NOT NULL',
        ]);

        return new self();
    }

    /**
     * Nests the TABLES statement in the current query
     *
     * @return self
     */
    public static function tables(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'tables'),
        ]);

        return new self();
    }

    /**
     * Nests the COLUMNS statement in the current query
     *
     * @return self
     */
    public static function columns(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'columns'),
        ]);

        return new self();
    }

    /**
     * Nests the IN statement in the current query.
     *
     * @param array<int, mixed>|null $values List of values.
     *
     * @return self
     */
    public static function in(?array $values = null): self
    {
        $query = is_array($values)
            ? str_replace('?', self::addCharacter($values), self::getKey(Driver::MYSQL, 'in'))
            : str_replace('(?)', '', self::getKey(Driver::MYSQL, 'in'));

        if (is_array($values)) {
            self::addRows($values);
        }

        self::addQueryList([$query]);

        return new self();
    }

    /**
     * Nests the NOT IN statement in the current query.
     *
     * @param array<int, mixed>|null $values List of values.
     *
     * @return self
     */
    public static function notIn(?array $values = null): self
    {
        $query = is_array($values)
            ? str_replace('?', self::addCharacter($values), self::getKey(Driver::MYSQL, 'in'))
            : str_replace('(?)', '', self::getKey(Driver::MYSQL, 'in'));

        if (is_array($values)) {
            self::addRows($values);
        }

        self::addQueryList([self::getKey(Driver::MYSQL, 'not'), $query]);
        return new self();
    }

    /**
     * Nests the CALL statement in the current query
     *
     * @param string $storedProcedure Stored Procedure Name
     * @param array<int, mixed> $rows List of values
     *
     * @return self
     */
    public static function call(string $storedProcedure, array $rows = []): self
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
            ")",
        ]);

        return new self();
    }

    /**
     * Nests the HAVING statement in the current query
     *
     * @return self
     */
    public static function having(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'having'),
        ]);

        return new self();
    }

    /**
     * Nests the SELECT DISTINCT statement in the current query
     *
     * @return self
     */
    public static function selectDistinct(): self
    {
        if (empty(self::$actualCode)) {
            self::$actualCode = uniqid('code-');
        }

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;

        /** @phpstan-ignore-next-line */
        $stringColumns = self::addColumns(func_get_args());

        if (empty(self::$table)) {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'select'),
                self::getKey(Driver::MYSQL, 'distinct'),
                " {$stringColumns}",
                self::getKey(Driver::MYSQL, 'from'),
                ' ',
                self::$view,
            ]);
        } else {
            self::addQueryList([
                self::getKey(Driver::MYSQL, 'select'),
                self::getKey(Driver::MYSQL, 'distinct'),
                " {$stringColumns}",
                self::getKey(Driver::MYSQL, 'from'),
                ' ',
                self::$table,
            ]);
        }

        return new self();
    }

    /**
     * Nests the BETWEEN statement in the current query
     *
     * @param mixed $between Value between
     * @param mixed $and Value and
     *
     * @return self
     */
    public static function between(mixed $between, mixed $and): self
    {
        self::addRows([$between, $and]);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'between'),
            ' ?',
            self::getKey(Driver::MYSQL, 'and'),
            ' ? ',
        ]);

        return new self();
    }

    /**
     * Nests the LIKE statement in the current query
     *
     * @param string $like [Preference value]
     *
     * @return self
     */
    public static function like(string $like): self
    {
        self::addRows([
            $like,
        ]);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'like'),
            ' ',
            self::addCharacter([
                $like,
            ]),
        ]);

        return new self();
    }

    /**
     * Nests the GROUP BY statement in the current query
     *
     * @return self
     */
    public static function groupBy(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'group-by'),
            ' ',
            /** @phpstan-ignore-next-line */
            self::addColumns(func_get_args()),
        ]);

        return new self();
    }

    /**
     * Nests the LIMIT statement in the current query
     *
     * @param int $start Start limit
     * @param int|null $limit End limit
     *
     * @return self
     */
    public static function limit(int $start, ?int $limit = null): self
    {
        $items = [
            $start,
        ];

        self::addRows([
            $start,
        ]);

        if (null != $limit) {
            $items[] = $limit;

            self::addRows([
                $limit,
            ]);
        }

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'limit'),
            ' ',
            self::addCharacter($items)
        ]);

        return new self();
    }

    /**
     * Nests the ASC statement in the current query
     *
     * @param bool $isString Determines whether to nest the ASC statement in the
     * current query or return the statement
     *
     * @return self|string
     */
    public static function asc(bool $isString = false): self|string
    {
        if ($isString) {
            return self::getKey(Driver::MYSQL, 'asc');
        }

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'asc'),
        ]);

        return new self();
    }

    /**
     * Nests the DESC statement in the current query
     *
     * @param bool $isString Determines whether to nest the DESC statement in the
     * current query or return the statement
     *
     * @return self|string
     */
    public static function desc(bool $isString = false): self|string
    {
        if ($isString) {
            return self::getKey(Driver::MYSQL, 'desc');
        }

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'desc'),
        ]);

        return new self();
    }

    /**
     * Nests the ORDER BY statement in the current query
     *
     * @return self
     */
    public static function orderBy(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'order-by'),
            ' ',
            /** @phpstan-ignore-next-line */
            self::addColumns(func_get_args()),
        ]);

        return new self();
    }

    /**
     * Nests the INNER statement in the current query
     *
     * @return self
     */
    public static function inner(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'inner'),
        ]);

        return new self();
    }

    /**
     * Nests the LEFT statement in the current query
     *
     * @return self
     */
    public static function left(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'left'),
        ]);

        return new self();
    }

    /**
     * Nests the RIGHT statement in the current query
     *
     * @return self
     */
    public static function right(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'right'),
        ]);

        return new self();
    }

    /**
     * Nests the JOIN statement in the current query.
     *
     * @param string $table Table name.
     * @param string $valueFrom Column from.
     * @param string $valueTo Column to.
     *
     * @return self
     */
    public static function join(string $table, string $valueFrom, string $valueTo): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'join'),
            " {$table}",
            self::getKey(Driver::MYSQL, 'on'),
            " {$valueFrom} = {$valueTo}",
        ]);

        return new self();
    }

    /**
     * Gets the column
     *
     * @param string $column Column name
     * @param string $table Table name
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
     * @param string $column Column name
     * @param string $table Table name
     *
     * @return self
     */
    public static function column(string $column, string $table = ''): self
    {
        self::addQueryList('' === $table ? [' ', trim($column)] : [' ', trim("{$table}.{$column}")]);

        return new self();
    }

    /**
     * Nests the MIN statement in the current query
     *
     * @param string $column Column name
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
     * @param string $column Column name
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
     * @param string $column Column name
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
     * @param string $column Column name
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
     * @param string $column Column name
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
     * @param string $column Column name
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
     * @param string $column Column name
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
     * @param string $column Column name
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
     * @param string $column Column name
     * @param int|null $length Length
     *
     * @return self
     */
    public static function int(string $column, ?int $length = null): self
    {
        if (null === $length) {
            self::addQueryList([
                " {$column}",
                str_replace('(?)', '', self::getKey(Driver::MYSQL, 'int')),
            ]);
        } else {
            self::addQueryList([
                " {$column}",
                str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'int')),
            ]);
        }

        return new self();
    }

    /**
     * Nests the BIGINT statement in the current query
     *
     * @param string $column Column name
     * @param int|null $length Length
     *
     * @return self
     */
    public static function bigInt(string $column, ?int $length = null): self
    {
        if (null === $length) {
            self::addQueryList([
                " {$column}",
                str_replace('(?)', '', self::getKey(Driver::MYSQL, 'bigint')),
            ]);
        } else {
            self::addQueryList([
                " {$column}",
                str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'bigint')),
            ]);
        }

        return new self();
    }

    /**
     * Nests the DECIMAL statement in the current query
     *
     * @param string $column Column name
     * @param int|null $digits Leftover Digits
     * @param int|null $bytes Number of Bytes
     *
     * @return self
     *
     * @link https://dev.mysql.com/doc/refman/9.3/en/precision-math-decimal-characteristics.html
     */
    public static function decimal(string $column, ?int $digits = null, ?int $bytes = null): self
    {
        if (null === $digits && null === $bytes) {
            self::addQueryList([
                " {$column}",
                self::getKey(Driver::MYSQL, 'decimal'),
            ]);
        } else {
            self::addQueryList([
                " {$column}",
                self::getKey(Driver::MYSQL, 'decimal'),
                "({$digits}, {$bytes})"
            ]);
        }

        return new self();
    }

    /**
     * Nests the DOUBLE statement in the current query
     *
     * @param string $column Column name
     *
     * @return self
     */
    public static function double(string $column): self
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'double'),
        ]);

        return new self();
    }

    /**
     * Nests the FLOAT statement in the current query
     *
     * @param string $column Column name
     *
     * @return self
     */
    public static function float(string $column): self
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'float'),
        ]);

        return new self();
    }

    /**
     * Nests the MEDIUMINT statement in the current query
     *
     * @param string $column Column name
     * @param int $length Length
     *
     * @return self
     */
    public static function mediumInt(string $column, int $length): self
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'mediumint')),
        ]);

        return new self();
    }

    /**
     * Nests the REAL statement in the current query
     *
     * @param string $column Column name
     *
     * @return self
     */
    public static function real(string $column): self
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'real'),
        ]);

        return new self();
    }

    /**
     * Nests the SMALLINT statement in the current query
     *
     * @param string $column Column name
     * @param int $length Length
     *
     * @return self
     */
    public static function smallInt(string $column, int $length): self
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'smallint')),
        ]);

        return new self();
    }

    /**
     * Nests the TINYINT statement in the current query
     *
     * @param string $column Column name
     * @param int $length Length
     *
     * @return self
     */
    public static function tinyInt(string $column, int $length): self
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'tinyint')),
        ]);

        return new self();
    }

    /**
     * Nests the BLOB statement in the current query
     *
     * @param string $column Column name
     *
     * @return self
     */
    public static function blob(string $column): self
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'blob'),
        ]);

        return new self();
    }

    /**
     * Nests the VARBINARY statement in the current query
     *
     * @param string $column Column name
     * @param string|int $length length
     *
     * @return self
     */
    public static function varBinary(string $column, string|int $length = 'MAX'): self
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'varbinary')),
        ]);

        return new self();
    }

    /**
     * Nests the CHAR statement in the current query
     *
     * @param string $column Column name
     * @param int $length Length
     *
     * @return self
     */
    public static function char(string $column, int $length): self
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'char')),
        ]);

        return new self();
    }

    /**
     * Nests the JSON statement in the current query
     *
     * @param string $column Column name
     *
     * @return self
     */
    public static function json(string $column): self
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'json'),
        ]);

        return new self();
    }

    /**
     * Nests the NCHAR statement in the current query
     *
     * @param string $column Column name
     * @param int $length Length
     *
     * @return self
     */
    public static function nchar(string $column, int $length): self
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'nchar')),
        ]);

        return new self();
    }

    /**
     * Nests the NVARCHAR statement in the current query
     *
     * @param string $column Column name
     * @param int $length Length
     *
     * @return self
     */
    public static function nvarchar(string $column, int $length): self
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'nvarchar')),
        ]);

        return new self();
    }

    /**
     * Nests the VARCHAR statement in the current query
     *
     * @param string $column Column name
     * @param int $length Length
     *
     * @return self
     */
    public static function varchar(string $column, int $length): self
    {
        self::addQueryList([
            " {$column}",
            str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'varchar')),
        ]);

        return new self();
    }

    /**
     * Nests the LONGTEXT statement in the current query
     *
     * @param string $column Column name
     *
     * @return self
     */
    public static function longText(string $column): self
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'longtext'),
        ]);

        return new self();
    }

    /**
     * Nests the MEDIUMTEXT statement in the current query
     *
     * @param string $column Column name
     *
     * @return self
     */
    public static function mediumText(string $column): self
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'mediumtext'),
        ]);

        return new self();
    }

    /**
     * Nests the TEXT statement in the current query
     *
     * @param string $column Column name
     *
     * @return self
     */
    public static function text(string $column): self
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'text'),
        ]);

        return new self();
    }

    /**
     * Nests the TINYTEXT statement in the current query
     *
     * @param string $column Column name
     *
     * @return self
     */
    public static function tinyText(string $column): self
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'tinytext'),
        ]);

        return new self();
    }

    /**
     * Nests the ENUM statement in the current query
     *
     * @param string $column Column name
     * @param array<int, string> $options Options
     *
     * @return self
     */
    public static function enum(string $column, array $options): self
    {
        $split = array_map(fn ($op) => "'{$op}'", $options);

        self::addQueryList([
            " {$column}",
            str_replace('?', implode(', ', $split), self::getKey(Driver::MYSQL, 'enum')),
        ]);

        return new self();
    }

    /**
     * Nests the DATE statement in the current query
     *
     * @param string $column Column name
     * @param bool $isString Defines whether to get a string from the DATE statement
     * with the column
     *
     * @return self|string
     */
    public static function date(string $column, bool $isString = false): self|string
    {
        if (!$isString) {
            self::addQueryList([
                " {$column}",
                self::getKey(Driver::MYSQL, 'date'),
            ]);
        } else {
            return self::getKey(Driver::MYSQL, 'date') . "($column)";
        }

        return new self();
    }

    /**
     * Nests the TIME statement in the current query
     *
     * @param string $column Column name
     *
     * @return self
     */
    public static function time(string $column): self
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'time'),
        ]);

        return new self();
    }

    /**
     * Nests the TIMESTAMP statement in the current query
     *
     * @param string $column Column name
     *
     * @return self
     */
    public static function timeStamp(string $column): self
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'timestamp'),
        ]);

        return new self();
    }

    /**
     * Nests the DATETIME statement in the current query
     *
     * @param string $column Column name
     *
     * @return self
     */
    public static function dateTime(string $column): self
    {
        self::addQueryList([
            " {$column}",
            self::getKey(Driver::MYSQL, 'datetime'),
        ]);

        return new self();
    }
}
