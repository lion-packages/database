<?php

declare(strict_types=1);

namespace Lion\Database\Drivers\Schema;

use Closure;
use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Database\Drivers\MySQL as DriverMySQL;
use Lion\Database\Helpers\Constants\MySQLConstants;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Database\Interface\RunDatabaseProcessesInterface;
use Lion\Database\Traits\ConnectionInterfaceTrait;
use Lion\Database\Traits\RunInterfaceTrait;
use PDOException;
use stdClass;

/**
 * Provides methods to perform direct operations on the MySQL database structure
 *
 * Key Features:
 *
 * * Schema management: Allows you to create, modify and delete database schemas
 * * Table creation: Facilitates the creation of new tables in the database,
 * specifying columns, data types and restrictions
 * * Table modification: Allows you to modify the structure of existing tables,
 * adding, modifying or eliminating columns and restrictions
 * * Table Deletion: Provides methods to safely delete tables from the database
 * * Indexing: Facilitates the creation and deletion of indexes on tables to
 * improve query performance
 * * Management of primary and foreign keys: Allows you to define and modify
 * primary and foreign keys in the tables
 *
 * This class provides a convenient interface to interact directly with the
 * MySQL database structure, making it easier to manage and manipulate it from
 * the application
 *
 * @property bool $in [Enable the configuration of the properties to implement
 * the IN statement]
 *
 * @package Lion\Database\Drivers\Schema
 */
class MySQL extends Connection implements DatabaseConfigInterface, RunDatabaseProcessesInterface
{
    use ConnectionInterfaceTrait;
    use RunInterfaceTrait;

    /**
     * [Enable the configuration of the properties to implement the IN
     * statement]
     *
     * @var bool $in
     */
    private static bool $in = false;

    /**
     * {@inheritdoc}
     */
    public static function execute(): stdClass
    {
        return parent::mysql(function (): stdClass {
            self::prepare(self::$sql);

            if (!self::$stmt->execute()) {
                throw new PDOException(self::$stmt->errorInfo()[2], 500);
            }

            self::clean();

            return (object) [
                'code' => 200,
                'status' => 'success',
                'message' => self::$message,
            ];
        });
    }

    /**
     * Generate the query to create a database
     *
     * @param string $database [Database name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Generate the query to delete a database
     *
     * @param string $database [Database name]
     *
     * @return MySQL
     */
    public static function dropDatabase(string $database): MySQL
    {
        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            self::getKey(Driver::MYSQL, 'drop'),
            self::getKey(Driver::MYSQL, 'database'),
            " `{$database}`"
        ]);

        return new static();
    }

    /**
     * Generate the query to create a table
     *
     * @param string $table [Table name]
     * @param Closure $tableBody [Table body]
     *
     * @return MySQL
     */
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
            self::getKey(Driver::MYSQL, 'set') . ' = ' . MySQLConstants::UTF8MB4,
            self::getKey(Driver::MYSQL, 'collate') . ' = ',
            MySQLConstants::UTF8MB4_SPANISH_CI . '; --REPLACE-INDEXES--',
        ]);

        $tableBody();

        self::buildTable();

        return new static();
    }

    /**
     * Delete a table from the database
     *
     * @param string $table [Table name]
     *
     * @return MySQL
     */
    public static function dropTable(string $table): MySQL
    {
        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            self::getKey(Driver::MYSQL, 'drop'),
            self::getKey(Driver::MYSQL, 'table'),
            ' ' . self::$dbname . ".{$table};",
        ]);

        return new static();
    }

    /**
     * Dropping tables from the database
     *
     * @return MySQL
     */
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
            'SET FOREIGN_KEY_CHECKS = 1;',
        ]);

        return new static();
    }

    /**
     * Empty a database table
     *
     * @param string $table [Table name]
     * @param bool $enableForeignKeyChecks [defines whether to verify foreign
     * keys]
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
                'SET foreign_key_checks = 1;',
            ]);
        } else {
            self::addNewQueryList([
                self::getKey(Driver::MYSQL, 'truncate'),
                self::getKey(Driver::MYSQL, 'table'),
                ' ' . self::$dbname . ".{$table};",
            ]);
        }

        return new static();
    }

    /**
     * Create a stored procedure
     *
     * @param string $storedProcedure [Stored procedure]
     * @param Closure $storeProcedureParams [Parameters]
     * @param Closure $storeProcedureBegin [Stored Procedure SQL Query]
     *
     * @return MySQL
     */
    public static function createStoreProcedure(
        string $storedProcedure,
        Closure $storeProcedureParams,
        Closure $storeProcedureBegin
    ): MySQL {
        self::$isProcedure = true;

        self::$table = $storedProcedure;

        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            self::getKey(Driver::MYSQL, 'drop'),
            self::getKey(Driver::MYSQL, 'procedure'),
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'exists'),
            " `{$storedProcedure}`;",
            self::getKey(Driver::MYSQL, 'create'),
            self::getKey(Driver::MYSQL, 'procedure'),
            " `{$storedProcedure}` (--REPLACE-PARAMS--)",
        ]);

        $storeProcedureParams();

        self::buildTable();

        self::addQueryList([self::getKey(Driver::MYSQL, 'begin')]);

        $storeProcedureBegin(
            (new DriverMySQL())
                ->run(self::$connections)
                ->isSchema()
                ->enableInsert(true)
        );

        self::addQueryList([
            ';',
            self::getKey(Driver::MYSQL, 'end'),
            ';',
        ]);

        return new static();
    }

    /**
     * Delete a stored procedure
     *
     * @param string $storedProcedure [Stored procedure]
     *
     * @return MySQL
     */
    public static function dropStoreProcedure(string $storedProcedure): MYSQL
    {
        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            self::getKey(Driver::MYSQL, 'drop'),
            self::getKey(Driver::MYSQL, 'procedure'),
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'exists'),
            " `{$storedProcedure}`;"
        ]);

        return new static();
    }

    /**
     * Create a view
     *
     * @param string $view [View name]
     * @param Closure $viewBody [View body]
     *
     * @return MySQL
     */
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

        $viewBody((new DriverMySQL())
            ->run(self::$connections)
            ->isSchema()
            ->enableInsert(true));

        return new static();
    }

    /**
     * Delete a view
     *
     * @param string $view [View name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the IN statement to the current query
     *
     * @return MySQL
     */
    public static function in(): MySQL
    {
        self::$in = true;

        return new static();
    }

    /**
     * Add the PRIMARY KEY statement to the current query
     *
     * @return MySQL
     */
    public static function primaryKey(): MySQL
    {
        self::$columns[self::$table][self::$actualColumn]['primary'] = true;

        self::$columns[self::$table][self::$actualColumn]['indexes'][] = str_replace(
            '?',
            self::$actualColumn,
            self::getKey(Driver::MYSQL, 'primary-key')
        );

        return new static();
    }

    /**
     * Add the AUTO INCREMENT statement to the current query
     *
     * @return MySQL
     */
    public static function autoIncrement(): MySQL
    {
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = true;

        return new static();
    }

    /**
     * Add the NOT NULL statement to the current query
     *
     * @return MySQL
     */
    public static function notNull(): MySQL
    {
        self::$columns[self::$table][self::$actualColumn]['null'] = false;

        return new static();
    }

    /**
     * Add the NULL statement to the current query
     *
     * @return MySQL
     */
    public static function null(): MySQL
    {
        self::$columns[self::$table][self::$actualColumn]['null'] = true;

        return new static();
    }

    /**
     * Add the COMMENT statement to the current query
     *
     * @param string $comment [Comment description]
     *
     * @return MySQL
     */
    public static function comment(string $comment): MySQL
    {
        self::$columns[self::$table][self::$actualColumn]['comment'] = true;

        self::$columns[self::$table][self::$actualColumn]['comment-description'] = $comment;

        return new static();
    }

    /**
     * Add the UNIQUE statement to the current query
     *
     * @return MySQL
     */
    public static function unique(): MySQL
    {
        $unique = self::getKey(Driver::MYSQL, 'unique') . self::getKey(Driver::MYSQL, 'index');

        $unique .= ' ' . self::$actualColumn . '_UNIQUE' . ' (' . self::$actualColumn . ' ASC)';

        self::$columns[self::$table][self::$actualColumn]['unique'] = true;

        self::$columns[self::$table][self::$actualColumn]['indexes'][] = $unique;

        return new static();
    }

    /**
     * Add the DEFAULT statement to the current query
     *
     * @param mixed|null $default [Default value]
     *
     * @return MySQL
     */
    public static function default(mixed $default = null): MySQL
    {
        if (!isset(self::$columns[self::$table][self::$actualColumn]['default'])) {
            self::$columns[self::$table][self::$actualColumn]['default'] = true;
        }

        self::$columns[self::$table][self::$actualColumn]['default-value'][] = $default;

        return new static();
    }

    /**
     * Add the FOREIGN statement to the current query
     *
     * @param string $table [Table name]
     * @param string $column [Reference column]
     *
     * @return MySQL
     */
    public static function foreign(string $table, string $column): MySQL
    {
        $relationColumn = self::$table . '_' . self::$actualColumn . '_FK';

        $indexed = "ADD INDEX {$relationColumn}_idx (" . self::$actualColumn . ' ASC)';

        $constraint = "ADD CONSTRAINT {$relationColumn} FOREIGN KEY (" . self::$actualColumn . ') REFERENCES ';

        $constraint .= self::$dbname . ".{$table}" . " ({$column}) ON DELETE RESTRICT ON UPDATE RESTRICT";

        self::$columns[self::$table][self::$actualColumn]['foreign']['index'] = $indexed;

        self::$columns[self::$table][self::$actualColumn]['foreign']['constraint'] = $constraint;

        return new static();
    }

    /**
     * Add the INT statement to the current query
     *
     * @param string $name [Column name]
     * @param int|null $length [Length]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the BIGINT statement to the current query
     *
     * @param string $name [Column name]
     * @param int|null $length [Length]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the DECIMAL statement to the current query
     *
     * @param string $name [Column name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the DOUBLE statement to the current query
     *
     * @param string $name [Column name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the FLOAT statement to the current query
     *
     * @param string $name [Column name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the MEDIUMINT statement to the current query
     *
     * @param string $name [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the REAL statement to the current query
     *
     * @param string $name [Column name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the SMALLINT statement to the current query
     *
     * @param string $name [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the TINYINT statement to the current query
     *
     * @param string $name [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the BLOB statement to the current query
     *
     * @param string $name [Column name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the VARBINARY statement to the current query
     *
     * @param string $name [Column name]
     * @param string|int $length [Length]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the CHAR statement to the current query
     *
     * @param string $name [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the JSON statement to the current query
     *
     * @param string $name [Column name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the NCHAR statement to the current query
     *
     * @param string $name [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the NVARCHAR statement to the current query
     *
     * @param string $name [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the VARCHAR statement to the current query
     *
     * @param string $name [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the LONGTEXT statement to the current query
     *
     * @param string $name [Column name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the MEDIUMTEXT statement to the current query
     *
     * @param string $name [Column name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the TEXT statement to the current query
     *
     * @param string $name [Column name]
     * @param int $length [Length]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the TINYTEXT statement to the current query
     *
     * @param string $name [Column name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the ENUM statement to the current query
     *
     * @param string $name [Column name]
     * @param array<int, string> $options [Options]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the DATE statement to the current query
     *
     * @param string $name [Column name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the TIME statement to the current query
     *
     * @param string $name [Column name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the TIMESTAMP statement to the current query
     *
     * @param string $name [Column name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Add the DATETIME statement to the current query
     *
     * @param string $name [Column name]
     *
     * @return MySQL
     */
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

        return new static();
    }

    /**
     * Nests the ON UPDATE statement in the current query
     *
     * @param string $onUpdate [Nested parameter in ON UPDATE]
     *
     * @return string
     */
    public static function onUpdate(string $onUpdate): string
    {
        return self::getKey(Driver::MYSQL, 'on') . self::getKey(Driver::MYSQL, 'update') . ' ' . $onUpdate;
    }
}
