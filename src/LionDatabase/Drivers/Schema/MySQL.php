<?php

declare(strict_types=1);

namespace Lion\Database\Drivers\Schema;

use Closure;
use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Database\Drivers\MySQL as DriverMySQL;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Database\Interface\RunDatabaseProcessesInterface;

class MySQL extends Connection implements DatabaseConfigInterface, RunDatabaseProcessesInterface
{
    private static bool $in = false;

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
    public static function execute(): object
    {
        return parent::mysql(function () {
            self::prepare(self::$sql);
            self::$stmt->execute();
            self::clean();

            return (object) ['status' => 'success', 'message' => self::$message];
        });
    }

    public static function createDatabase(string $database): MySQL
    {
        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'create'),
            self::getKey(Driver::MYSQL, 'database'),
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'not'),
            self::getKey(Driver::MYSQL, 'exists'),
            " `{$database}`"
        ]);

        return new static;
    }

    public static function dropDatabase(string $database): MySQL
    {
        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            self::getKey(Driver::MYSQL, 'drop'),
            self::getKey(Driver::MYSQL, 'database'),
            " `{$database}`"
        ]);

        return new static;
    }

    public static function createTable(string $table, Closure $tableBody): MySQL
    {
        self::$table = $table;

        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            self::getKey(Driver::MYSQL, 'drop'),
            self::getKey(Driver::MYSQL, 'table'),
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'exists'),
            ' ' . self::$dbname . ".{$table};",
            self::getKey(Driver::MYSQL, 'create'),
            self::getKey(Driver::MYSQL, 'table'),
            ' ' . self::$dbname . ".{$table} (--REPLACE-PARAMS--)",
            self::getKey(Driver::MYSQL, 'engine') . ' = INNODB',
            self::getKey(Driver::MYSQL, 'default'),
            self::getKey(Driver::MYSQL, 'character'),
            self::getKey(Driver::MYSQL, 'set') . ' = ' . DriverMySQL::UTF8MB4,
            self::getKey(Driver::MYSQL, 'collate') . ' = ' . DriverMySQL::UTF8MB4_SPANISH_CI . '; --REPLACE-INDEXES--'
        ]);

        $tableBody();
        self::buildTable();

        return new static;
    }

    public static function dropTable(string $table): MySQL
    {
        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            self::getKey(Driver::MYSQL, 'drop'),
            self::getKey(Driver::MYSQL, 'table'),
            ' ' . self::$dbname . ".{$table};",
        ]);

        return new static;
    }

    public static function dropTables(): MySQL
    {
        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            'SET FOREIGN_KEY_CHECKS = 0;',
            'SET @tables_db = NULL;',
            'SELECT GROUP_CONCAT(table_name) INTO @tables_db FROM information_schema.tables',
            ' WHERE table_schema = (SELECT DATABASE());',
            "SET @search_tbl = CONCAT('DROP TABLE IF EXISTS ', @tables_db);",
            'PREPARE stmt FROM @search_tbl;',
            'EXECUTE stmt;',
            'DEALLOCATE PREPARE stmt;',
            'SET FOREIGN_KEY_CHECKS = 1;'
        ]);

        return new static;
    }

    /**
     * Empty a database table
     *
     * @param  string $table [Table name]
     * @param  bool|boolean $enableForeignKeyChecks [defines whether to verify
     * foreign keys]
     *
     * @return MySQL
     */
    public static function truncateTable(string $table, bool $enableForeignKeyChecks = false): MySQL
    {
        if (!$enableForeignKeyChecks) {
            self::addNewQueryList([
                'SET foreign_key_checks = 0;',
                self::getKey(Driver::MYSQL, 'truncate'),
                self::getKey(Driver::MYSQL, 'table'),
                ' ' . self::$dbname . ".{$table};",
                'SET foreign_key_checks = 1;'
            ]);
        } else {
            self::addNewQueryList([
                self::getKey(Driver::MYSQL, 'truncate'),
                self::getKey(Driver::MYSQL, 'table'),
                ' ' . self::$dbname . ".{$table};",
            ]);
        }

        return new static;
    }

    public static function createStoreProcedure(
        string $storeProcedure,
        Closure $storeProcedureParams,
        Closure $storeProcedureBegin
    ): MySQL {
        self::$isProcedure = true;
        self::$table = $storeProcedure;

        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            self::getKey(Driver::MYSQL, 'drop'),
            self::getKey(Driver::MYSQL, 'procedure'),
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'exists'),
            " `{$storeProcedure}`;",
            self::getKey(Driver::MYSQL, 'create'),
            self::getKey(Driver::MYSQL, 'procedure'),
            " `{$storeProcedure}` (--REPLACE-PARAMS--)"
        ]);

        $storeProcedureParams();
        self::buildTable();
        self::addQueryList([self::getKey(Driver::MYSQL, 'begin')]);
        $storeProcedureBegin((new DriverMySQL())->run(self::$connections)->isSchema()->enableInsert(true));
        self::addQueryList([';', self::getKey(Driver::MYSQL, 'end'), ';']);

        return new static;
    }

    public static function dropStoreProcedure(string $storeProcedure): MYSQL
    {
        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            self::getKey(Driver::MYSQL, 'drop'),
            self::getKey(Driver::MYSQL, 'procedure'),
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'exists'),
            " `{$storeProcedure}`;"
        ]);

        return new static;
    }

    public static function createView(string $view, Closure $viewBody): MySQL
    {
        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            self::getKey(Driver::MYSQL, 'create'),
            self::getKey(Driver::MYSQL, 'or'),
            self::getKey(Driver::MYSQL, 'replace'),
            self::getKey(Driver::MYSQL, 'view'),
            " `{$view}`",
            self::getKey(Driver::MYSQL, 'as')
        ]);

        $viewBody((new DriverMySQL())->run(self::$connections)->isSchema()->enableInsert(true));

        return new static;
    }

    public static function dropView(string $view): MySQL
    {
        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            self::getKey(Driver::MYSQL, 'drop'),
            self::getKey(Driver::MYSQL, 'view'),
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'exists'),
            " `{$view}`;"
        ]);

        return new static;
    }

    public static function in(): MySQL
    {
        self::$in = true;

        return new static;
    }

    public static function primaryKey(): MySQL
    {
        self::$columns[self::$table][self::$actualColumn]['primary'] = true;

        self::$columns[self::$table][self::$actualColumn]['indexes'][] = str_replace(
            '?',
            self::$actualColumn,
            self::getKey(Driver::MYSQL, 'primary-key')
        );

        return new static;
    }

    public static function autoIncrement(): MySQL
    {
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = true;

        return new static;
    }

    public static function notNull(): MySQL
    {
        self::$columns[self::$table][self::$actualColumn]['null'] = false;

        return new static;
    }

    public static function null(): MySQL
    {
        self::$columns[self::$table][self::$actualColumn]['null'] = true;

        return new static;
    }

    public static function comment(string $comment): MySQL
    {
        self::$columns[self::$table][self::$actualColumn]['comment'] = true;
        self::$columns[self::$table][self::$actualColumn]['comment-description'] = $comment;

        return new static;
    }

    public static function unique(): MySQL
    {
        $unique = self::getKey(Driver::MYSQL, 'unique') . self::getKey(Driver::MYSQL, 'index');
        $unique .= ' ' . self::$actualColumn . '_UNIQUE' . ' (' . self::$actualColumn . ' ASC)';

        self::$columns[self::$table][self::$actualColumn]['unique'] = true;
        self::$columns[self::$table][self::$actualColumn]['indexes'][] = $unique;

        return new static;
    }

    public static function default(mixed $default = null): MySQL
    {
        self::$columns[self::$table][self::$actualColumn]['default'] = true;
        self::$columns[self::$table][self::$actualColumn]['default-value'] = $default;

        return new static;
    }

    public static function foreign(string $table, string $column): MySQL
    {
        $relationColumn = self::$table . '_' . self::$actualColumn . '_FK';

        $indexed = "ADD INDEX {$relationColumn}_idx (" . self::$actualColumn . ' ASC)';
        $constraint = "ADD CONSTRAINT {$relationColumn} FOREIGN KEY (" . self::$actualColumn . ') REFERENCES ';
        $constraint .= self::$dbname . ".{$table}" . " ({$column}) ON DELETE RESTRICT ON UPDATE RESTRICT";

        self::$columns[self::$table][self::$actualColumn]['foreign']['index'] = $indexed;
        self::$columns[self::$table][self::$actualColumn]['foreign']['constraint'] = $constraint;

        return new static;
    }

    public static function int(string $name, ?int $length = null): MySQL
    {
        $column = '';
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        if (null === $length) {
            $column = str_replace('(?)', '', self::getKey(Driver::MYSQL, 'int'));
        } else {
            $column = str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'int'));
        }

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function bigInt(string $name, ?int $length = null): MySQL
    {
        $column = '';
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        if (null === $length) {
            $column = str_replace('(?)', '', self::getKey(Driver::MYSQL, 'bigint'));
        } else {
            $column = str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'bigint'));
        }

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function decimal(string $name): MySQL
    {
        $column = self::getKey(Driver::MYSQL, 'decimal');
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function double(string $name): MySQL
    {
        $column = self::getKey(Driver::MYSQL, 'double');
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function float(string $name): MySQL
    {
        $column = self::getKey(Driver::MYSQL, 'float');
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function mediumInt(string $name, int $length): MySQL
    {
        $column = str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'mediumint'));
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function real(string $name): MySQL
    {
        $column = self::getKey(Driver::MYSQL, 'real');
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function smallInt(string $name, int $length): MySQL
    {
        $column = str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'smallint'));
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function tinyInt(string $name, int $length): MySQL
    {
        $column = str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'tinyint'));
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function blob(string $name): MySQL
    {
        $column = self::getKey(Driver::MYSQL, 'blob');
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function varBinary(string $name, string|int $length = 'MAX'): MySQL
    {
        $column = str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'varbinary'));
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function char(string $name, int $length): MySQL
    {
        $column = str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'char'));
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function json(string $name): MySQL
    {
        $column = self::getKey(Driver::MYSQL, 'json');
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function nchar(string $name, int $length): MySQL
    {
        $column = str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'nchar'));
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function nvarchar(string $name, int $length): MySQL
    {
        $column = str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'nvarchar'));
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function varchar(string $name, int $length): MySQL
    {
        $column = str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'varchar'));
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function longText(string $name): MySQL
    {
        $column = self::getKey(Driver::MYSQL, 'longtext');
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function mediumText(string $name): MySQL
    {
        $column = self::getKey(Driver::MYSQL, 'mediumtext');
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function text(string $name, int $length): MySQL
    {
        $column = str_replace('?', (string) $length, self::getKey(Driver::MYSQL, 'text'));
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function tinyText(string $name): MySQL
    {
        $column = self::getKey(Driver::MYSQL, 'tinytext');
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function enum(string $name, array $options): MySQL
    {
        $split = array_map(fn ($op) => "'{$op}'", $options);
        $column = str_replace('?', implode(', ', $split), self::getKey(Driver::MYSQL, 'enum'));
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function date(string $name): MySQL
    {
        $column = self::getKey(Driver::MYSQL, 'date');
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function time(string $name): MySQL
    {
        $column = self::getKey(Driver::MYSQL, 'time');
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function timeStamp(string $name): MySQL
    {
        $column = self::getKey(Driver::MYSQL, 'timestamp');
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }

    public static function dateTime(string $name): MySQL
    {
        $column = self::getKey(Driver::MYSQL, 'datetime');
        self::$actualColumn = $name;
        $in = self::$in;
        self::$in = false;

        self::$columns[self::$table][self::$actualColumn]['primary'] = false;
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = false;
        self::$columns[self::$table][self::$actualColumn]['unique'] = false;
        self::$columns[self::$table][self::$actualColumn]['comment'] = false;
        self::$columns[self::$table][self::$actualColumn]['default'] = false;
        self::$columns[self::$table][self::$actualColumn]['null'] = false;
        self::$columns[self::$table][self::$actualColumn]['in'] = $in;
        self::$columns[self::$table][self::$actualColumn]['type'] = $column;
        self::$columns[self::$table][self::$actualColumn]['column'] = "{$name}{$column}";

        return new static;
    }
}
