<?php

declare(strict_types=1);

namespace LionDatabase\Drivers;

use Closure;
use LionDatabase\Connection;
use PDO;

class MySQL extends Connection
{
    public static function run(array $connections): void
    {
        self::$connections = $connections;
        self::$activeConnection = self::$connections['default'];
        self::$dbname = self::$connections['connections'][self::$connections['default']]['dbname'];
    }

    public static function connection(string $connectionName): MySQL
    {
        self::$activeConnection = $connectionName;
        self::$dbname = self::$connections['connections'][$connectionName]['dbname'];

        return new static;
    }

    public static function transaction(bool $isTransaction = true): MySQL
    {
        self::$isTransaction = $isTransaction;

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

    public static function end(string $end = ";"): MySQL
    {
        self::addQueryList([$end]);

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

    public static function recursive(string $name): MySQL|string
    {
        self::addQueryList([
            self::getKey('mysql', 'recursive'),
            " {$name}",
            self::getKey('mysql', 'as')
        ]);

        return new static;
    }

    public static function with(bool $return = false): MySQL|string
    {
        if ($return) {
            return self::getKey('mysql', 'with');
        }

        self::addQueryList([self::getKey('mysql', 'with')]);

        return new static;
    }

    public static function table(string $table, bool $option = false, bool $nest = false): MySQL
    {
        if (!$option) {
            if (!$nest) {
                self::$table = self::$dbname . '.' . $table;
            } else {
                self::addQueryList([
                    self::getKey('mysql', 'table'),
                    ' ',
                    self::$dbname,
                    '.',
                    $table
                ]);
            }
        } else {
            if (!$nest) {
                self::$table = $table;
            } else {
                self::addQueryList([
                    self::getKey('mysql', 'table'),
                    ' ',
                    $table
                ]);
            }
        }

        return new static;
    }

    public static function view(string $view, bool $option = false, bool $nest = false): MySQL
    {
        if (!$option) {
            if (!$nest) {
                self::$view = self::$dbname . '.' . $view;
            } else {
                self::addQueryList([
                    self::getKey('mysql', 'view'),
                    ' ',
                    self::$dbname,
                    '.',
                    $view
                ]);
            }
        } else {
            if (!$nest) {
                self::$view = $view;
            } else {
                self::addQueryList([
                    self::getKey('mysql', 'view'),
                    ' ',
                    $view
                ]);
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
        self::addQueryList([
            self::getKey('mysql', 'create'),
            self::getKey('mysql', 'table'),
            ' ',
            self::$table
        ]);

        return new static;
    }

    public static function show(): MySQL
    {
        self::$actualCode = uniqid();
        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;
        self::$sql = self::getKey('mysql', 'show');

        return new static;
    }

    public static function from(string $from = null): MySQL
    {
        if (empty($from)) {
            self::addQueryList([
                self::getKey('mysql', 'from'),
                ' ',
                (empty(self::$table) ? self::$view : self::$table)
            ]);
        } else {
            self::addQueryList([
                self::getKey('mysql', 'from'),
                ' ',
                $from
            ]);
        }

        return new static;
    }

    public static function indexes(): MySQL
    {
        self::addQueryList([
            self::getKey('mysql', 'index'),
            self::getKey('mysql', 'from'),
            ' ',
            self::$table
        ]);

        return new static;
    }

    public static function drop(): MySQL
    {
        if (empty(self::$table)) {
            self::addQueryList([
                self::getKey('mysql', 'drop'),
                self::getKey('mysql', 'view'),
                ' ',
                self::$view
            ]);
        } else {
            self::addQueryList([
                self::getKey('mysql', 'drop'),
                self::getKey('mysql', 'table'),
                ' ',
                self::$table
            ]);
        }

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
        self::$actualCode = uniqid();
        self::$message = 'Execution finished';
        self::addQueryList([$sql]);

        return new static;
    }

    public static function bulk(array $columns, array $rows): MySQL
    {
        self::$actualCode = uniqid();
        self::$message = 'Rows inserted successfully';

        foreach ($rows as $row) {
            self::addRows($row);
        }

        self::addNewQueryList([
            self::getKey('mysql', 'insert'),
            ' ',
            self::$table,
            ' (',
            self::addColumns($columns),
            ')',
            self::getKey('mysql', 'values'),
            ' ',
            self::addCharacterBulk($rows)
        ]);

        return new static;
    }

    public static function in(): MySQL
    {
        $columns = func_get_args();
        self::addRows($columns);
        self::addQueryList([str_replace('?', self::addCharacter($columns), self::getKey('mysql', 'in'))]);

        return new static;
    }

    public static function call(string $store_procedure, array $rows = []): MySQL
    {
        self::$actualCode = uniqid();
        self::$message = 'Procedure executed successfully';
        self::addRows($rows);

        self::addQueryList([
            self::getKey('mysql', 'call'),
            ' ',
            self::$dbname,
            ".{$store_procedure}(",
            self::addCharacter($rows),
            ")"
        ]);

        return new static;
    }

    public static function delete(): MySQL
    {
        self::$actualCode = uniqid();
        self::$message = 'Rows deleted successfully';

        self::addQueryList([
            self::getKey('mysql', 'delete'),
            self::getKey('mysql', 'from'),
            ' ',
            self::$table
        ]);

        return new static;
    }

    public static function update(array $rows = []): MySQL
    {
        self::$actualCode = uniqid();
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
        self::$actualCode = uniqid();
        self::$message = 'Rows inserted successfully';
        self::addRows($rows);

        self::addQueryList([
            self::getKey('mysql', 'insert'),
            self::getKey('mysql', 'into'),
            ' ',
            self::$table,
            '  (',
            self::addColumns(array_keys($rows)),
            ')',
            self::getKey('mysql', 'values'),
            ' (',
            self::addCharacterAssoc($rows),
            ')'
        ]);

        return new static;
    }

    public static function having(string $column, ?string $value = null): MySQL
    {
        self::addRows([$value]);
        self::addQueryList([self::getKey('mysql', 'having'), " {$column}"]);

        return new static;
    }

    public static function select(): MySQL
    {
        self::$actualCode = uniqid();
        self::$fetchMode[self::$actualCode] = PDO::FETCH_OBJ;
        $stringColumns = self::addColumns(func_get_args());

        if (empty(self::$table)) {
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
        self::$actualCode = uniqid();
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

        self::addQueryList([
            self::getKey('mysql', 'between'),
            ' ?',
            self::getKey('mysql', 'and'),
            ' ? '
        ]);

        return new static;
    }

    public static function like(string $like): MySQL
    {
        self::addRows([$like]);

        self::addQueryList([
            self::getKey('mysql', 'like'),
            ' ',
            self::addCharacter([$like])
        ]);

        return new static;
    }

    public static function groupBy(): MySQL
    {
        self::addQueryList([
            self::getKey('mysql', 'group-by'),
            ' ',
            self::addColumns(func_get_args())
        ]);

        return new static;
    }

    public static function limit(int $start, ?int $limit = null): MySQL
    {
        $items = [$start];

        if (!empty($limit)) {
            $items[] = $limit;
        }

        self::addRows([$start]);

        self::addQueryList([
            self::getKey('mysql', 'limit'),
            ' ',
            self::addCharacter($items)
        ]);

        if (!empty($limit)) {
            self::addRows([$limit]);
        }

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
        self::addQueryList([
            self::getKey('mysql', 'order-by'),
            ' ',
            self::addColumns(func_get_args())
        ]);

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

    public static function join(string $table, string $value_from, string $value_up_to, bool $option = false): MySQL
    {
        if (!$option) {
            self::addQueryList([
                self::getKey('mysql', 'join'),
                ' ',
                self::$dbname,
                ".{$table}",
                self::getKey('mysql', 'on'),
                " {$value_from}={$value_up_to}"
            ]);
        } else {
            self::addQueryList([
                self::getKey('mysql', 'join'),
                " {$table}",
                self::getKey('mysql', 'on'),
                " {$value_from}={$value_up_to}"
            ]);
        }

        return new static;
    }

    public static function where(Closure|string $value_type, mixed $value = null): MySQL
    {
        if (is_string($value_type)) {
            self::addQueryList(
                !empty($value) ? [self::getKey('mysql', 'where'), " {$value_type}"] : [self::getKey('mysql', 'where')]
            );

            if (!empty($value)) {
                self::addRows([$value]);
            }
        } else {
            self::resolveNestedQuery($value_type, $value, self::getKey('mysql', 'where'));
        }

        return new static;
    }

    public static function and(Closure|string $valueType, mixed $value = null): MySQL
    {
        if (is_string($valueType)) {
            self::addQueryList(
                !empty($value) ? [self::getKey('mysql', 'and'), " {$valueType}"] : [self::getKey('mysql', 'and')]
            );

            if (!empty($value)) {
                self::addRows([$value]);
            }
        } else {
            self::resolveNestedQuery($valueType, $value, self::getKey('mysql', 'and'));
        }

        return new static;
    }

    public static function or(Closure|string $value_type, mixed $value = null): MySQL
    {
        if (is_string($value_type)) {
            self::addQueryList(
                !empty($value) ? [self::getKey('mysql', 'or') . " {$value_type}"] : [self::getKey('mysql', 'or')]
            );

            if (!empty($value)) {
                self::addRows([$value]);
            }
        } else {
            self::resolveNestedQuery($value_type, $value, self::getKey('mysql', 'or'));
        }

        return new static;
    }

    private static function resolveNestedQuery(Closure $query, mixed $value, string $word): void
    {
        self::addQueryList([$word]);
        self::openGroup();

        if (is_array($value)) {
            self::addQueryList([' ' . array_key_first($value)]);
            $values = array_values($value);
            self::addRows([reset($values)]);
        }

        $query();
        self::closeGroup();
    }

    public static function column(string $value, string $table = ""): string
    {
        return empty($table) ? trim($value) : trim("{$table}.{$value}");
    }

    public static function equalTo(string $column): string
    {
        return trim($column . "=?");
    }

    public static function greaterThan(string $column): string
    {
        return trim($column . " > ?");
    }

    public static function lessThan(string $column): string
    {
        return trim($column . " < ?");
    }

    public static function greaterThanOrEqualTo(string $column): string
    {
        return trim($column . " >= ?");
    }

    public static function lessThanOrEqualTo(string $column): string
    {
        return trim($column . " <= ?");
    }

    public static function notEqualTo(string $column): string
    {
        return trim($column . " <> ?");
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
}
