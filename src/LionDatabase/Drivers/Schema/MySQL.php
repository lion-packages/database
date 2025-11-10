<?php

declare(strict_types=1);

namespace Lion\Database\Drivers\Schema;

use Closure;
use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Database\Drivers\MySQL as DriverMySQL;
use Lion\Database\Helpers\Constants\MySQLConstants;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Database\Interface\ExecuteInterface;
use Lion\Database\Interface\GetQueryStringInterface;
use Lion\Database\Traits\ConnectionInterfaceTrait;
use Lion\Database\Traits\GetQueryStringInterfaceTrait;
use Lion\Database\Traits\RunInterfaceTrait;
use PDOException;
use stdClass;

/**
 * Provides methods to perform direct operations on the MySQL database structure.
 *
 * Key Features:
 *
 * * Schema management: Allows you to create, modify and delete database schemas.
 * * Table creation: Facilitates the creation of new tables in the database,
 * specifying columns, data types and restrictions.
 * * Table modification: Allows you to modify the structure of existing tables,
 * adding, modifying or eliminating columns and restrictions.
 * * Table Deletion: Provides methods to safely delete tables from the database.
 * * Indexing: Facilitates the creation and deletion of indexes on tables to
 * improve query performance.
 * * Management of primary and foreign keys: Allows you to define and modify
 * primary and foreign keys in the tables.
 *
 * This class provides a convenient interface to interact directly with the
 * MySQL database structure, making it easier to manage and manipulate it from
 * the application.
 */
class MySQL extends Connection implements DatabaseConfigInterface, ExecuteInterface, GetQueryStringInterface
{
    use ConnectionInterfaceTrait;
    use GetQueryStringInterfaceTrait;
    use RunInterfaceTrait;

    /**
     * Enable the configuration of the properties to implement the IN statement.
     *
     * @var bool $in
     */
    private static bool $in = false;

    /**
     * {@inheritdoc}
     */
    public static function execute(): int|stdClass
    {
        return parent::process(function (): int|stdClass {
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
     * Generate the query to create a database.
     *
     * @param string $database Database name.
     *
     * @return self
     */
    public static function createDatabase(string $database): self
    {
        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'create'),
            self::getKey(Driver::MYSQL, 'database'),
            self::getKey(Driver::MYSQL, 'if'),
            self::getKey(Driver::MYSQL, 'not'),
            self::getKey(Driver::MYSQL, 'exists'),
            " `{$database}`"
        ]);

        return new self();
    }

    /**
     * Generate the query to delete a database.
     *
     * @param string $database Database name.
     *
     * @return self
     */
    public static function dropDatabase(string $database): self
    {
        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            self::getKey(Driver::MYSQL, 'drop'),
            self::getKey(Driver::MYSQL, 'database'),
            " `{$database}`"
        ]);

        return new self();
    }

    /**
     * Generate the query to create a table.
     *
     * @param string $table Table name.
     * @param Closure $tableBody Table body.
     *
     * @return self
     */
    public static function createTable(string $table, Closure $tableBody): self
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

        return new self();
    }

    /**
     * Delete a table from the database.
     *
     * @param string $table Table name.
     *
     * @return self
     */
    public static function dropTable(string $table): self
    {
        self::addNewQueryList([
            self::getKey(Driver::MYSQL, 'use'),
            " `" . self::$dbname . "`;",
            self::getKey(Driver::MYSQL, 'drop'),
            self::getKey(Driver::MYSQL, 'table'),
            ' ' . self::$dbname . ".{$table};",
        ]);

        return new self();
    }

    /**
     * Dropping tables from the database.
     *
     * @return self
     */
    public static function dropTables(): self
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

        return new self();
    }

    /**
     * Empty a database table.
     *
     * @param string $table Table name.
     * @param bool $enableForeignKeyChecks defines whether to verify foreign keys.
     *
     * @return self
     */
    public static function truncateTable(string $table, bool $enableForeignKeyChecks = false): self
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

        return new self();
    }

    /**
     * Create a stored procedure.
     *
     * @param string $storedProcedure Stored procedure.
     * @param Closure $params Parameters.
     * @param Closure $begin Stored Procedure SQL Query.
     *
     * @return self
     */
    public static function createStoredProcedure(string $storedProcedure, Closure $params, Closure $begin): self
    {
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

        $params();

        self::buildTable();

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'begin'),
        ]);

        /** @var string $activeConnection */
        $activeConnection = self::$activeConnection;

        $begin(
            DriverMySQL::run(self::$connections)
                ->isSchema()
                ->enableInsert(true)
                ->connection($activeConnection)
        );

        self::addQueryList([
            ';',
            self::getKey(Driver::MYSQL, 'end'),
            ';',
        ]);

        return new self();
    }

    /**
     * Delete a stored procedure.
     *
     * @param string $storedProcedure Stored procedure.
     *
     * @return self
     */
    public static function dropStoreProcedure(string $storedProcedure): self
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

        return new self();
    }

    /**
     * Create a view.
     *
     * @param string $view View name.
     * @param Closure $viewBody View body.
     *
     * @return self
     */
    public static function createView(string $view, Closure $viewBody): self
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

        /** @var string $activeConnection */
        $activeConnection = self::$activeConnection;

        $viewBody(
            DriverMySQL::run(self::$connections)
                ->isSchema()
                ->enableInsert(true)
                ->connection($activeConnection)
        );

        return new self();
    }

    /**
     * Delete a view.
     *
     * @param string $view View name.
     *
     * @return self
     */
    public static function dropView(string $view): self
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

        return new self();
    }

    /**
     * Add the IN statement to the current query.
     *
     * @return self
     */
    public static function in(): self
    {
        self::$in = true;

        return new self();
    }

    /**
     * Add the PRIMARY KEY statement to the current query.
     *
     * @return self
     */
    public static function primaryKey(): self
    {
        self::$columns[self::$table][self::$actualColumn]['primary'] = true;

        self::$columns[self::$table][self::$actualColumn]['indexes'][] = str_replace(
            '?',
            self::$actualColumn,
            self::getKey(Driver::MYSQL, 'primary-key')
        );

        return new self();
    }

    /**
     * Add the AUTO INCREMENT statement to the current query.
     *
     * @return self
     */
    public static function autoIncrement(): self
    {
        self::$columns[self::$table][self::$actualColumn]['auto-increment'] = true;

        return new self();
    }

    /**
     * Add the NOT NULL statement to the current query.
     *
     * @return self
     */
    public static function notNull(): self
    {
        self::$columns[self::$table][self::$actualColumn]['null'] = false;

        return new self();
    }

    /**
     * Add the NULL statement to the current query.
     *
     * @return self
     */
    public static function null(): self
    {
        self::$columns[self::$table][self::$actualColumn]['null'] = true;

        return new self();
    }

    /**
     * Add the COMMENT statement to the current query.
     *
     * @param string $comment Comment description.
     *
     * @return self
     */
    public static function comment(string $comment): self
    {
        self::$columns[self::$table][self::$actualColumn]['comment'] = true;

        self::$columns[self::$table][self::$actualColumn]['comment-description'] = $comment;

        return new self();
    }

    /**
     * Add the UNIQUE statement to the current query.
     *
     * @return self
     */
    public static function unique(): self
    {
        $unique = self::getKey(Driver::MYSQL, 'unique') . self::getKey(Driver::MYSQL, 'index');

        $unique .= ' ' . self::$actualColumn . '_UNIQUE' . ' (' . self::$actualColumn . ' ASC)';

        self::$columns[self::$table][self::$actualColumn]['unique'] = true;

        self::$columns[self::$table][self::$actualColumn]['indexes'][] = $unique;

        return new self();
    }

    /**
     * Add the DEFAULT statement to the current query.
     *
     * @param string|int|float|bool|null $default Default value.
     *
     * @return self
     */
    public static function default(string|int|float|bool|null $default): self
    {
        if (!self::$columns[self::$table][self::$actualColumn]['default']) {
            self::$columns[self::$table][self::$actualColumn]['default'] = true;
        }

        self::$columns[self::$table][self::$actualColumn]['default-value'][] = $default;

        return new self();
    }

    /**
     * Add the FOREIGN statement to the current query.
     *
     * @param string $table Table name.
     * @param string $column Reference column.
     *
     * @return self
     */
    public static function foreign(string $table, string $column): self
    {
        $relationColumn = self::$table . '_' . self::$actualColumn . '_FK';

        $indexed = "ADD INDEX {$relationColumn}_idx (" . self::$actualColumn . ' ASC)';

        $constraint = "ADD CONSTRAINT {$relationColumn} FOREIGN KEY (" . self::$actualColumn . ') REFERENCES ';

        $constraint .= self::$dbname . ".{$table}" . " ({$column}) ON DELETE RESTRICT ON UPDATE RESTRICT";

        self::$columns[self::$table][self::$actualColumn]['foreign']['index'] = $indexed;

        self::$columns[self::$table][self::$actualColumn]['foreign']['constraint'] = $constraint;

        return new self();
    }

    /**
     * Add the INT statement to the current query.
     *
     * @param string $name Column name.
     * @param int|null $length Length.
     *
     * @return self
     */
    public static function int(string $name, ?int $length = null): self
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

        return new self();
    }

    /**
     * Add the BIGINT statement to the current query.
     *
     * @param string $name Column name.
     * @param int|null $length Length.
     *
     * @return self
     */
    public static function bigInt(string $name, ?int $length = null): self
    {
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

        return new self();
    }

    /**
     * Add the DECIMAL statement to the current query.
     *
     * @param string $columnName Column name.
     * @param int|null $digits Leftover Digits.
     * @param int|null $bytes Number of Bytes.
     *
     * @return self
     *
     * @link https://dev.mysql.com/doc/refman/9.3/en/precision-math-decimal-characteristics.html
     */
    public static function decimal(string $columnName, ?int $digits = null, ?int $bytes = null): self
    {
        if (null === $digits && null === $bytes) {
            $column = self::getKey(Driver::MYSQL, 'decimal');
        } else {
            $column = self::getKey(Driver::MYSQL, 'decimal') . "({$digits}, {$bytes})";
        }

        self::$actualColumn = $columnName;

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

        self::$columns[self::$table][self::$actualColumn]['column'] = "{$columnName}{$column}";

        return new self();
    }

    /**
     * Add the DOUBLE statement to the current query.
     *
     * @param string $name Column name.
     *
     * @return self
     */
    public static function double(string $name): self
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

        return new self();
    }

    /**
     * Add the FLOAT statement to the current query.
     *
     * @param string $name Column name.
     *
     * @return self
     */
    public static function float(string $name): self
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

        return new self();
    }

    /**
     * Add the MEDIUMINT statement to the current query.
     *
     * @param string $name Column name.
     * @param int $length Length.
     *
     * @return self
     */
    public static function mediumInt(string $name, int $length): self
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

        return new self();
    }

    /**
     * Add the REAL statement to the current query.
     *
     * @param string $name Column name.
     *
     * @return self
     */
    public static function real(string $name): self
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

        return new self();
    }

    /**
     * Add the SMALLINT statement to the current query.
     *
     * @param string $name Column name.
     * @param int $length Length.
     *
     * @return self
     */
    public static function smallInt(string $name, int $length): self
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

        return new self();
    }

    /**
     * Add the TINYINT statement to the current query.
     *
     * @param string $name Column name.
     * @param int $length Length.
     *
     * @return self
     */
    public static function tinyInt(string $name, int $length): self
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

        return new self();
    }

    /**
     * Add the BLOB statement to the current query.
     *
     * @param string $name Column name.
     *
     * @return self
     */
    public static function blob(string $name): self
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

        return new self();
    }

    /**
     * Add the VARBINARY statement to the current query.
     *
     * @param string $name Column name.
     * @param string|int $length Length.
     *
     * @return self
     */
    public static function varBinary(string $name, string|int $length = 'MAX'): self
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

        return new self();
    }

    /**
     * Add the CHAR statement to the current query.
     *
     * @param string $name Column name.
     * @param int $length Length.
     *
     * @return self
     */
    public static function char(string $name, int $length): self
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

        return new self();
    }

    /**
     * Add the JSON statement to the current query.
     *
     * @param string $name Column name.
     *
     * @return self
     */
    public static function json(string $name): self
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

        return new self();
    }

    /**
     * Add the NCHAR statement to the current query.
     *
     * @param string $name Column name.
     * @param int $length Length.
     *
     * @return self
     */
    public static function nchar(string $name, int $length): self
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

        return new self();
    }

    /**
     * Add the NVARCHAR statement to the current query.
     *
     * @param string $name Column name.
     * @param int $length Length.
     *
     * @return self
     */
    public static function nvarchar(string $name, int $length): self
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

        return new self();
    }

    /**
     * Add the VARCHAR statement to the current query.
     *
     * @param string $name Column name.
     * @param int $length Length.
     *
     * @return self
     */
    public static function varchar(string $name, int $length): self
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

        return new self();
    }

    /**
     * Add the LONGTEXT statement to the current query.
     *
     * @param string $name Column name.
     *
     * @return self
     */
    public static function longText(string $name): self
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

        return new self();
    }

    /**
     * Add the MEDIUMTEXT statement to the current query.
     *
     * @param string $name Column name.
     *
     * @return self
     */
    public static function mediumText(string $name): self
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

        return new self();
    }

    /**
     * Add the TEXT statement to the current query.
     *
     * @param string $columnName Column name.
     *
     * @return self
     *
     * @link https://dev.mysql.com/doc/refman/9.3/en/blob.html
     */
    public static function text(string $columnName): self
    {
        $column = self::getKey(Driver::MYSQL, 'text');

        self::$actualColumn = $columnName;

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

        self::$columns[self::$table][self::$actualColumn]['column'] = "{$columnName}{$column}";

        return new self();
    }

    /**
     * Add the TINYTEXT statement to the current query.
     *
     * @param string $name Column name.
     *
     * @return self
     */
    public static function tinyText(string $name): self
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

        return new self();
    }

    /**
     * Add the ENUM statement to the current query.
     *
     * @param string $name Column name.
     * @param array<int, string> $options Options.
     *
     * @return self
     */
    public static function enum(string $name, array $options): self
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

        return new self();
    }

    /**
     * Add the DATE statement to the current query.
     *
     * @param string $name Column name.
     *
     * @return self
     */
    public static function date(string $name): self
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

        return new self();
    }

    /**
     * Add the TIME statement to the current query.
     *
     * @param string $name Column name.
     *
     * @return self
     */
    public static function time(string $name): self
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

        return new self();
    }

    /**
     * Add the TIMESTAMP statement to the current query.
     *
     * @param string $name Column name.
     *
     * @return self
     */
    public static function timeStamp(string $name): self
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

        return new self();
    }

    /**
     * Add the DATETIME statement to the current query.
     *
     * @param string $name Column name.
     *
     * @return self
     */
    public static function dateTime(string $name): self
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

        return new self();
    }

    /**
     * Nests the ON UPDATE statement in the current query.
     *
     * @param string $onUpdate Nested parameter in ON UPDATE.
     *
     * @return string
     */
    public static function onUpdate(string $onUpdate): string
    {
        return self::getKey(Driver::MYSQL, 'on') . self::getKey(Driver::MYSQL, 'update') . ' ' . $onUpdate;
    }
}
