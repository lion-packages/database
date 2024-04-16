<?php

declare(strict_types=1);

namespace Lion\Database\Helpers\Constants;

/**
 * Defines the MySQL word dictionary
 *
 * @package Lion\Database\Helpers\Constants
 */
class MySQLConstants
{
    /**
     * [An auto-updated column is automatically updated to the current timestamp
     * when the value of any other column in the row is changed from its current
     * value. An auto-updated column remains unchanged if all other columns are
     * set to their current values. To prevent an auto-updated column from
     * updating when other columns change, explicitly set it to its current
     * value. To update an auto-updated column even when other columns do not
     * change, explicitly set it to the value it should have (for example, set
     * it to CURRENT_TIMESTAMP)]
     *
     * @const CURRENT_TIMESTAMP
     */
    const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';

    /**
     * [The utf8mb4 Character Set (4-Byte UTF-8 Unicode Encoding)]
     *
     * The utf8mb4 character set has these characteristics:
     *
     * * Supports BMP and supplementary characters
     * * Requires a maximum of four bytes per multibyte character
     *
     * utf8mb4 contrasts with the utf8mb3 character set, which supports only BMP
     * characters and uses a maximum of three bytes per character:
     *
     * * For a BMP character, utf8mb4 and utf8mb3 have identical storage
     * characteristics: same code values, same encoding, same length
     * * For a supplementary character, utf8mb4 requires four bytes to store it,
     * whereas utf8mb3 cannot store the character at all. When converting
     * utf8mb3 columns to utf8mb4, you need not worry about converting
     * supplementary characters because there are none
     *
     * utf8mb4 is a superset of utf8mb3, so for an operation such as the
     * following concatenation, the result has character set utf8mb4 and the
     * collation of utf8mb4_col
     *
     * @const UTF8MB4
     */
    const UTF8MB4 = 'UTF8MB4';

    /**
     * [Collation UTF8MB4_SPANISH_CI]
     *
     * @const UTF8MB4_SPANISH_CI
     */
    const UTF8MB4_SPANISH_CI = 'UTF8MB4_SPANISH_CI';

    /**
     * [MySQL word list]
     *
     * @const KEYWORDS
     */
    const KEYWORDS = [
        'delimiter' => ' DELIMITER',
        'not' => ' NOT',
        'truncate' => ' TRUNCATE',
        'innodb' => ' INNODB',
        'charset' => ' CHARSET',
        'status' => ' STATUS',
        'replace' => ' REPLACE',
        'end' => ' END',
        'begin' => ' BEGIN',
        'exists' => ' EXISTS',
        'if' => ' IF',
        'procedure' => ' PROCEDURE',
        'use' => ' USE',
        'engine' => ' ENGINE',
        'collate' => ' COLLATE',
        'character' => ' CHARACTER',
        'schema' => ' SCHEMA',
        'database' => ' DATABASE',
        'full' => ' FULL',
        'with' => ' WITH',
        'recursive' => ' RECURSIVE',
        'year' => ' YEAR(?)',
        'month' => ' MONTH(?)',
        'day' => ' DAY(?)',
        'in' => ' IN(?)',
        'where' => ' WHERE',
        'as' => ' AS',
        'and' => ' AND',
        'or' => ' OR',
        'between' => ' BETWEEN',
        'select' => ' SELECT',
        'from' => ' FROM',
        'join' => ' JOIN',
        'on' => ' ON',
        'left' => ' LEFT',
        'right' => ' RIGHT',
        'inner' => ' INNER',
        'insert' => ' INSERT',
        'into' => ' INTO',
        'values' => ' VALUES',
        'update' => ' UPDATE',
        'set' => ' SET',
        'delete' => ' DELETE',
        'call' => ' CALL',
        'like' => ' LIKE',
        'group-by' => ' GROUP BY',
        'asc' => ' ASC',
        'desc' => ' DESC',
        'order-by' => ' ORDER BY',
        'count' => ' COUNT(?)',
        'max' => ' MAX(?)',
        'min' => ' MIN(?)',
        'sum' => ' SUM(?)',
        'avg' => ' AVG(?)',
        'limit' => ' LIMIT',
        'having' => ' HAVING',
        'show' => ' SHOW',
        'tables' => ' TABLES',
        'columns' => ' COLUMNS',
        'drop' => ' DROP',
        'table' => ' TABLE',
        'index' => ' INDEX',
        'unique' => ' UNIQUE',
        'create' => ' CREATE',
        'view' => ' VIEW',
        'concat' => ' CONCAT(*)',
        'union' => ' UNION',
        'all' => ' ALL',
        'distinct' => ' DISTINCT',
        'offset' => ' OFFSET',
        'primary-key' => ' PRIMARY KEY (?)',
        'primary' => ' PRIMARY',
        'auto-increment' => ' AUTO_INCREMENT',
        'comment' => ' COMMENT',
        'default' => ' DEFAULT',
        'is-not-null' => ' IS NOT NULL',
        'is-null' => ' IS NULL',
        'null' => ' NULL',
        'not-null' => ' NOT NULL',
        'int' => ' INT(?)',
        'bigint' => ' BIGINT(?)',
        'decimal' => ' DECIMAL',
        'double' => ' DOUBLE',
        'float' => ' FLOAT',
        'mediumint' => ' MEDIUMINT(?)',
        'real' => ' REAL',
        'smallint' => ' SMALLINT(?)',
        'tinyint' => ' TINYINT(?)',
        'blob' => ' BLOB',
        'varbinary' => ' VARBINARY(?)',
        'char' => ' CHAR(?)',
        'json' => ' JSON',
        'nchar' => ' NCHAR(?)',
        'nvarchar' => ' NVARCHAR(?)',
        'varchar' => ' VARCHAR(?)',
        'longtext' => ' LONGTEXT',
        'mediumtext' => ' MEDIUMTEXT',
        'text' => ' TEXT(?)',
        'tinytext' => ' TINYTEXT',
        'enum' => ' ENUM(?)',
        'date' => ' DATE',
        'time' => ' TIME',
        'timestamp' => ' TIMESTAMP',
        'datetime' => ' DATETIME',
        'alter' => ' ALTER',
        'add' => ' ADD',
        'constraint' => ' CONSTRAINT',
        'key' => ' KEY',
        'foreign' => ' FOREIGN',
        'references' => ' REFERENCES',
        'restrict' => ' RESTRICT',
        'cascade' => ' CASCADE',
        'no' => ' NO',
        'action' => ' ACTION'
    ];
}
