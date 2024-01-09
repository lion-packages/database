<?php

declare(strict_types=1);

namespace LionDatabase\Drivers;

use Closure;
use LionDatabase\Connection;
use LionDatabase\Interface\DatabaseInterface;
use PDO;
use PDOException;

class MySQL extends Connection implements DatabaseInterface
{
    /**
     * {@inheritdoc}
     */
    public static function execute(): object
    {
        return parent::mysql(function() {
            $response = (object) ['status' => 'success', 'message' => self::$message];

            if (self::$isTransaction) {
                self::$message = 'Transaction executed successfully';
            }

            try {
                self::$listSql = array_map(
                    fn($value) => trim($value),
                    array_filter(explode(';', trim(self::$sql)), fn($value) => trim($value) != '')
                );

                $data_info_keys = array_keys(self::$dataInfo);

                if (count($data_info_keys) > 0) {
                    foreach ($data_info_keys as $key => $code) {
                        self::prepare(self::$listSql[$key]);
                        self::bindValue($code);
                        self::$stmt->execute();
                        self::$stmt->closeCursor();
                    }
                } else {
                    self::prepare(self::$sql);
                    self::bindValue(self::$actualCode);
                    self::$stmt->execute();
                }

                if (self::$isTransaction) {
                    self::$conn->commit();
                }

                self::clean();
                return $response;
            } catch (PDOException $e) {
                if (self::$isTransaction) {
                    self::$conn->rollBack();
                }

                self::clean();

                return (object) [
                    'status' => 'database-error',
                    'message' => $e->getMessage(),
                    'data' => (object) [
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ];
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function get(): array|object
    {
        return parent::mysql(function() {
            $responses = [];

            self::$listSql = array_map(
                fn($value) => trim($value),
                array_filter(explode(';', trim(self::$sql)), fn($value) => trim($value) != '')
            );

            try {
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
                    $request = self::$stmt->fetch();

                    if (!$request) {
                        if (count(self::$fetchMode) > 1) {
                            $responses[] = (object) ['status' => 'success', 'message' => 'No data available'];
                        } else {
                            $responses = (object) ['status' => 'success', 'message' => 'No data available'];
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
            } catch (PDOException $e) {
                self::clean();

                $responses[] = (object) [
                    'status' => 'database-error',
                    'message' => $e->getMessage(),
                    'data' => (object) [
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ];
            }

            return $responses;
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function getAll(): array|object
    {
        return parent::mysql(function() {
            $responses = [];

            self::$listSql = array_map(
                fn($value) => trim($value),
                array_filter(explode(';', trim(self::$sql)), fn($value) => trim($value) != '')
            );

            try {
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
                            $responses[] = (object) ['status' => 'success', 'message' => 'No data available'];
                        } else {
                            $responses = (object) ['status' => 'success', 'message' => 'No data available'];
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
            } catch (PDOException $e) {
                self::clean();

                $responses[] = (object) [
                    'status' => 'database-error',
                    'message' => $e->getMessage(),
                    'data' => (object) [
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ];
            }

            return $responses;
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function run(array $connections): MySQL
    {
        self::$connections = $connections;
        self::$activeConnection = self::$connections['default'];
        self::$dbname = self::$connections['connections'][self::$connections['default']]['dbname'];

        return new static;
    }

    /**
     * {@inheritdoc}
     */
    public static function connection(string $connectionName): MySQL
    {
        self::$activeConnection = $connectionName;
        self::$dbname = self::$connections['connections'][$connectionName]['dbname'];

        return new static;
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

    public static function truncate(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'truncate')]);

        return new static;
    }

    public static function autoIncrement(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'auto-increment')]);

        return new static;
    }

    public static function action(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'action')]);

        return new static;
    }

    public static function no(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'no')]);

        return new static;
    }

    public static function cascade(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'cascade')]);

        return new static;
    }

    public static function restrict(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'restrict')]);

        return new static;
    }

    public static function onDelete(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'on'), self::getKey('mysql', 'delete')]);

        return new static;
    }

    public static function onUpdate(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'on'), self::getKey('mysql', 'update')]);

        return new static;
    }

    public static function on(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'on')]);

        return new static;
    }

    public static function references(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'references')]);

        return new static;
    }

    public static function foreign(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'foreign')]);

        return new static;
    }

    public static function constraint(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'constraint')]);

        return new static;
    }

    public static function add(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'add')]);

        return new static;
    }

    public static function alter(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'alter')]);

        return new static;
    }

    public static function comment(string $value = ''): MySQL
    {
        if ('' === $value) {
            self::addQueryList([self::getKey('mysql', 'comment')]);
        } else {
            self::addQueryList([self::getKey('mysql', 'comment'), ' ', "'{$value}'"]);
        }

        return new static;
    }

    public static function unique(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'unique')]);

        return new static;
    }

    public static function primaryKey(string $value): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'primary'), self::getKey('mysql', 'key'), " ({$value})"]);

        return new static;
    }

    public static function key(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'key')]);

        return new static;
    }

    public static function primary(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'primary')]);

        return new static;
    }

    public static function engine(string $value = ''): MySQL
    {
        if ('' === $value) {
            self::addQueryList([self::getKey('mysql', 'engine')]);
        } else {
            self::addQueryList([self::getKey('mysql', 'engine'), ' = ', $value]);
        }

        return new static;
    }

    public static function notNull(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'not-null')]);

        return new static;
    }

    public static function null(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'null')]);

        return new static;
    }

    public static function innoDB(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'innodb')]);

        return new static;
    }

    public static function collate(string $value = ''): MySQL
    {
        if ('' === $value) {
            self::addQueryList([self::getKey('mysql', 'collate')]);
        } else {
            self::addQueryList([self::getKey('mysql', 'collate'), ' = ', $value]);
        }

        return new static;
    }

    public static function set(string $value = ''): MySQL
    {
        if ('' === $value) {
            self::addQueryList([self::getKey('mysql', 'set')]);
        } else {
            self::addQueryList([self::getKey('mysql', 'set'), ' = ', $value]);
        }

        return new static;
    }

    public static function character(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'character')]);

        return new static;
    }

    public static function default(string|int $value = ''): MySQL
    {
        if ('' === $value) {
            self::addQueryList([self::getKey('mysql', 'default')]);
        } else {
            self::addQueryList([self::getKey('mysql', 'default'), ' ', (is_string($value) ? "'{$value}'" : $value)]);
        }

        return new static;
    }

    public static function schema(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'schema')]);

        return new static;
    }

    public static function addQuery(string $query): MySQL
    {
        self::addQueryList([" {$query}"]);

        return new static;
    }

    public static function ifExists(string $exist): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'if'), self::getKey('mysql', 'exists'), " `{$exist}`"]);

        return new static;
    }

    public static function use(string $use): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'use'), " `{$use}`"]);

        return new static;
    }

    public static function begin(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'begin')]);

        return new static;
    }

    public static function end(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'end')]);

        return new static;
    }

    public static function create(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'create')]);

        return new static;
    }

    public static function procedure(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'procedure')]);

        return new static;
    }

    public static function status(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'status')]);

        return new static;
    }

    public static function closeQuery(string $close = ";"): MySQL
    {
        self::addQueryList([$close]);

        return new static;
    }

    public static function full(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'full')]);

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
        self::addQueryList([self::getKey('mysql', 'recursive'), " {$name}", self::getKey('mysql', 'as')]);

        return new static;
    }

    public static function with(bool $isString = false): MySQL|string
    {
        if ($isString) {
            return self::getKey('mysql', 'with');
        }

        self::addQueryList([self::getKey('mysql', 'with')]);

        return new static;
    }

    public static function table(string|bool $table = true, bool $withDatabase = true): MySQL
    {
        if (is_string($table)) {
            self::$table = !$withDatabase ? $table : self::$dbname . ".{$table}";
        } else {
            if ($table) {
                self::addQueryList([self::getKey('mysql', 'table')]);
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
                self::addQueryList([self::getKey('mysql', 'view')]);
            }
        }

        return new static;
    }

    public static function isNull(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'is-null')]);

        return new static;
    }

    public static function isNotNull(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'is-not-null')]);

        return new static;
    }

    public static function offset(int $increase = 0): MySQL
    {
        self::addRows([$increase]);
        self::addQueryList([self::getKey('mysql', 'offset'), ' ?']);

        return new static;
    }

    public static function unionAll(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'union'), self::getKey('mysql', 'all')]);

        return new static;
    }

    public static function union(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'union')]);

        return new static;
    }

    public static function as(string $column, string $as): string
    {
        return $column . self::getKey('mysql', 'as') . " {$as}";
    }

    public static function concat()
    {
        return str_replace('*', implode(', ', func_get_args()), self::getKey('mysql', 'concat'));
    }

    public static function createTable(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'create'), self::getKey('mysql', 'table'), ' ', self::$table]);

        return new static;
    }

    public static function show(): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid();
        }

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;
        self::$sql = self::getKey('mysql', 'show');

        return new static;
    }

    public static function from(string $from = null): MySQL
    {
        if (null === $from) {
            self::addQueryList([
                self::getKey('mysql', 'from'),
                ' ',
                ('' === trim(self::$table) ? self::$view : self::$table)
            ]);
        } else {
            self::addQueryList([self::getKey('mysql', 'from'), ' ', $from]);
        }

        return new static;
    }

    public static function index(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'index')]);

        return new static;
    }

    public static function drop(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'drop')]);

        return new static;
    }

    public static function constraints(): MySQL
    {
        self::addRows(explode('.', self::$table));

        self::addNewQueryList([
            self::getKey('mysql', 'select'),
            ' CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME',
            self::getKey('mysql', 'from'),
            ' information_schema.KEY_COLUMN_USAGE WHERE ',
            'TABLE_SCHEMA=? AND TABLE_NAME=? AND REFERENCED_COLUMN_NAME IS NOT NULL'
        ]);

        return new static;
    }

    public static function tables(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'tables')]);

        return new static;
    }

    public static function columns(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'columns')]);

        return new static;
    }

    public static function query(string $sql): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid();
        }

        self::addQueryList([$sql]);

        return new static;
    }

    public static function bulk(array $columns, array $rows): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid();
        }

        self::$message = 'Rows inserted successfully';

        foreach ($rows as $row) {
            self::addRows($row);
        }

        self::addQueryList([
            self::getKey('mysql', 'insert'),
            self::getKey('mysql', 'into'),
            ' ',
            self::$table,
            ' (',
            self::addColumns($columns),
            ')',
            self::getKey('mysql', 'values'),
            ' ',
            self::addCharacterBulk($rows, (self::$isSchema && self::$enableInsert))
        ]);

        return new static;
    }

    public static function in(?array $values = null): MySQL
    {
        if (is_array($values)) {
            self::addRows($values);
            self::addQueryList([str_replace('?', self::addCharacter($values), self::getKey('mysql', 'in'))]);
        } else {
            self::addQueryList([str_replace("(?)", '', self::getKey('mysql', 'in'))]);
        }

        return new static;
    }

    public static function call(string $storeProcedure, array $rows = []): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid();
        }

        self::$message = 'Procedure executed successfully';
        self::addRows($rows);

        self::addQueryList([
            self::getKey('mysql', 'call'),
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
            self::$actualCode = uniqid();
        }

        self::$message = 'Rows deleted successfully';

        self::addQueryList([self::getKey('mysql', 'delete'), self::getKey('mysql', 'from'), ' ', self::$table]);

        return new static;
    }

    public static function update(array $rows = []): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid();
        }

        self::$message = 'Rows updated successfully';
        self::addRows($rows);

        self::addQueryList([
            self::getKey('mysql', 'update'),
            ' ',
            self::$table,
            self::getKey('mysql', 'set'),
            ' ',
            self::addCharacterEqualTo($rows)
        ]);

        return new static;
    }

    public static function insert(array $rows = []): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid();
        }

        self::$message = 'Rows inserted successfully';
        self::addRows($rows);

        self::addQueryList([
            self::getKey('mysql', 'insert'),
            self::getKey('mysql', 'into'),
            ' ',
            self::$table,
            ' (',
            self::addColumns(array_keys($rows)),
            ')',
            self::getKey('mysql', 'values'),
            ' (',
            (
                !self::$isSchema
                    ? self::addCharacterAssoc($rows)
                    : self::addColumns(array_values($rows), true, (self::$isSchema && self::$enableInsert))
            ),
            ')'
        ]);

        return new static;
    }

    public static function having(string $column, mixed $value): MySQL
    {
        self::addRows([$value]);
        self::addQueryList([self::getKey('mysql', 'having'), " {$column}"]);

        return new static;
    }

    public static function select(): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid();
        }

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;
        $stringColumns = self::addColumns(func_get_args());

        if ('' === self::$table) {
            self::addQueryList([
                self::getKey('mysql', 'select'),
                " {$stringColumns}",
                self::getKey('mysql', 'from'),
                ' ',
                self::$view
            ]);
        } else {
            self::addQueryList([
                self::getKey('mysql', 'select'),
                " {$stringColumns}",
                self::getKey('mysql', 'from'),
                ' ',
                self::$table
            ]);
        }

        return new static;
    }

    public static function selectDistinct(): MySQL
    {
        if ('' === self::$actualCode) {
            self::$actualCode = uniqid();
        }

        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;
        $stringColumns = self::addColumns(func_get_args());

        if (empty(self::$table)) {
            self::addQueryList([
                self::getKey('mysql', 'select'),
                self::getKey('mysql', 'distinct'),
                " {$stringColumns}",
                self::getKey('mysql', 'from'),
                ' ',
                self::$view
            ]);
        } else {
            self::addQueryList([
                self::getKey('mysql', 'select'),
                self::getKey('mysql', 'distinct'),
                " {$stringColumns}",
                self::getKey('mysql', 'from'),
                ' ',
                self::$table
            ]);
        }

        return new static;
    }

    public static function between(mixed $between, mixed $and): MySQL
    {
        self::addRows([$between, $and]);

        self::addQueryList([self::getKey('mysql', 'between'), ' ?', self::getKey('mysql', 'and'), ' ? ']);

        return new static;
    }

    public static function like(string $like): MySQL
    {
        self::addRows([$like]);

        self::addQueryList([self::getKey('mysql', 'like'), ' ', self::addCharacter([$like])]);

        return new static;
    }

    public static function groupBy(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'group-by'), ' ', self::addColumns(func_get_args())]);

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

        self::addQueryList([self::getKey('mysql', 'limit'), ' ', self::addCharacter($items)]);

        return new static;
    }

    public static function asc(bool $isString = false): MySQL|string
    {
        if ($isString) {
            return self::getKey('mysql', 'asc');
        }

        self::addQueryList([self::getKey('mysql', 'asc')]);

        return new static;
    }

    public static function desc(bool $isString = false): MySQL|string
    {
        if ($isString) {
            return self::getKey('mysql', 'desc');
        }

        self::addQueryList([self::getKey('mysql', 'desc')]);

        return new static;
    }

    public static function orderBy(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'order-by'), ' ', self::addColumns(func_get_args())]);

        return new static;
    }

    public static function inner(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'inner')]);

        return new static;
    }

    public static function left(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'left')]);

        return new static;
    }

    public static function right(): MySQL
    {
        self::addQueryList([self::getKey('mysql', 'right')]);

        return new static;
    }

    public static function join(string $table, string $valueFrom, string $valueUpTo, bool $withAlias = true): MySQL
    {
        if ($withAlias) {
            self::addQueryList([
                self::getKey('mysql', 'join'),
                ' ',
                self::$dbname,
                ".{$table}",
                self::getKey('mysql', 'on'),
                " {$valueFrom} = {$valueUpTo}"
            ]);
        } else {
            self::addQueryList([
                self::getKey('mysql', 'join'),
                " {$table}",
                self::getKey('mysql', 'on'),
                " {$valueFrom} = {$valueUpTo}"
            ]);
        }

        return new static;
    }

    public static function where(Closure|bool $valueType = true): MySQL
    {
        if (is_callable($valueType)) {
            self::addQueryList([self::getKey('mysql', 'where')]);
            $valueType();
        } else {
            if ($valueType) {
                self::addQueryList([self::getKey('mysql', 'where')]);
            }
        }

        return new static;
    }

    public static function and(Closure|bool $valueType = true): MySQL
    {
        if (is_callable($valueType)) {
            self::addQueryList([self::getKey('mysql', 'and')]);
            $valueType();
        } else {
            if ($valueType) {
                self::addQueryList([self::getKey('mysql', 'and')]);
            }
        }

        return new static;
    }

    public static function or(Closure|bool $valueType = true): MySQL
    {
        if (is_callable($valueType)) {
            self::addQueryList([self::getKey('mysql', 'or')]);
            $valueType();
        } else {
            if ($valueType) {
                self::addQueryList([self::getKey('mysql', 'or')]);
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
        self::addRows([$value]);
        self::addQueryList([' ', trim($column . ' = ?')]);

        return new static;
    }

    public static function notEqualTo(string $column, mixed $value): MySQL
    {
        self::addRows([$value]);
        self::addQueryList([' ', trim($column . ' <> ?')]);

        return new static;
    }

    public static function greaterThan(string $column, mixed $value): MySQL
    {
        self::addRows([$value]);
        self::addQueryList([' ', trim($column . ' > ?')]);

        return new static;
    }

    public static function lessThan(string $column, mixed $value): MySQL
    {
        self::addRows([$value]);
        self::addQueryList([' ', trim($column . ' < ?')]);

        return new static;
    }

    public static function greaterThanOrEqualTo(string $column, mixed $value): MySQL
    {
        self::addRows([$value]);
        self::addQueryList([' ', trim($column . ' >= ?')]);

        return new static;
    }

    public static function lessThanOrEqualTo(string $column, mixed $value): MySQL
    {
        self::addRows([$value]);
        self::addQueryList([' ', trim($column . ' <= ?')]);

        return new static;
    }

    public static function min(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey('mysql', 'min')));
    }

    public static function max(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey('mysql', 'max')));
    }

    public static function avg(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey('mysql', 'avg')));
    }

    public static function sum(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey('mysql', 'sum')));
    }

    public static function count(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey('mysql', 'count')));
    }

    public static function day(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey('mysql', 'day')));
    }

    public static function month(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey('mysql', 'month')));
    }

    public static function year(string $column): string
    {
        return trim(str_replace('?', $column, self::getKey('mysql', 'year')));
    }

    public static function int(string $name, ?int $length = null): MySQL
    {
        if (null === $length) {
            self::addQueryList([" {$name}", str_replace('(?)', '', self::getKey('mysql', 'int'))]);
        } else {
            self::addQueryList([" {$name}", str_replace('?', (string) $length, self::getKey('mysql', 'int'))]);
        }

        return new static;
    }

    public static function bigInt(string $name, int $length): MySQL
    {
        self::addQueryList([" {$name}", str_replace('?', (string) $length, self::getKey('mysql', 'bigint'))]);

        return new static;
    }

    public static function decimal(string $name): MySQL
    {
        self::addQueryList([" {$name}", self::getKey('mysql', 'decimal')]);

        return new static;
    }

    public static function double(string $name): MySQL
    {
        self::addQueryList([" {$name}", self::getKey('mysql', 'double')]);

        return new static;
    }

    public static function float(string $name): MySQL
    {
        self::addQueryList([" {$name}", self::getKey('mysql', 'float')]);

        return new static;
    }

    public static function mediumInt(string $name, int $length = 5): MySQL
    {
        self::addQueryList([" {$name}", str_replace('?', (string) $length, self::getKey('mysql', 'mediumint'))]);

        return new static;
    }

    public static function real(string $name): MySQL
    {
        self::addQueryList([" {$name}", self::getKey('mysql', 'real')]);

        return new static;
    }

    public static function smallInt(string $name, int $length): MySQL
    {
        self::addQueryList([" {$name}", str_replace('?', (string) $length, self::getKey('mysql', 'smallint'))]);

        return new static;
    }

    public static function tinyInt(string $name, int $length): MySQL
    {
        self::addQueryList([" {$name}", str_replace('?', (string) $length, self::getKey('mysql', 'tinyint'))]);

        return new static;
    }

    public static function blob(string $name): MySQL
    {
        self::addQueryList([" {$name}", self::getKey('mysql', 'blob')]);

        return new static;
    }

    public static function varBinary(string $name, string|int $length = 'MAX'): MySQL
    {
        self::addQueryList([" {$name}", str_replace('?', (string) $length, self::getKey('mysql', 'varbinary'))]);

        return new static;
    }

    public static function char(string $name, int $length): MySQL
    {
        self::addQueryList([" {$name}", str_replace('?', (string) $length, self::getKey('mysql', 'char'))]);

        return new static;
    }

    public static function json(string $name): MySQL
    {
        self::addQueryList([" {$name}", self::getKey('mysql', 'json')]);

        return new static;
    }

    public static function nchar(string $name, int $length): MySQL
    {
        self::addQueryList([" {$name}", str_replace('?', (string) $length, self::getKey('mysql', 'nchar'))]);

        return new static;
    }

    public static function nvarchar(string $name, int $length): MySQL
    {
        self::addQueryList([" {$name}", str_replace('?', (string) $length, self::getKey('mysql', 'nvarchar'))]);

        return new static;
    }

    public static function varchar(string $name, int $length): MySQL
    {
        self::addQueryList([" {$name}", str_replace('?', (string) $length, self::getKey('mysql', 'varchar'))]);

        return new static;
    }

    public static function longText(string $name): MySQL
    {
        self::addQueryList([" {$name}", self::getKey('mysql', 'longtext')]);

        return new static;
    }

    public static function mediumText(string $name): MySQL
    {
        self::addQueryList([" {$name}", self::getKey('mysql', 'mediumtext')]);

        return new static;
    }

    public static function text(string $name, int $length): MySQL
    {
        self::addQueryList([" {$name}", str_replace('?', (string) $length, self::getKey('mysql', 'text'))]);

        return new static;
    }

    public static function tinyText(string $name): MySQL
    {
        self::addQueryList([" {$name}", self::getKey('mysql', 'tinytext')]);

        return new static;
    }

    public static function enum(string $name, array $options): MySQL
    {
        $split = array_map(fn($op) => "'{$op}'", $options);
        self::addQueryList([" {$name}", str_replace('?', implode(', ', $split), self::getKey('mysql', 'enum'))]);

        return new static;
    }

    public static function date(string $name): MySQL
    {
        self::addQueryList([" {$name}", self::getKey('mysql', 'date')]);

        return new static;
    }

    public static function time(string $name): MySQL
    {
        self::addQueryList([" {$name}", self::getKey('mysql', 'time')]);

        return new static;
    }

    public static function timeStamp(string $name): MySQL
    {
        self::addQueryList([" {$name}", self::getKey('mysql', 'timestamp')]);

        return new static;
    }

    public static function dateTime(string $name): MySQL
    {
        self::addQueryList([" {$name}", self::getKey('mysql', 'datetime')]);

        return new static;
    }
}
