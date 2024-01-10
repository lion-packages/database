<?php 

declare(strict_types=1);

namespace Lion\Database\Helpers;

use Lion\Database\Helpers\Constants\MySQLConstantsTrait;

trait KeywordsTrait
{
	use MySQLConstantsTrait;

    private const DATABASE_KEYWORDS = [
        'mysql' => self::MYSQL_KEYWORDS
    ];
    const UTF8MB4 = 'UTF8MB4';
    const UTF8MB4_SPANISH_CI = 'UTF8MB4_SPANISH_CI';
    protected const DATA_TYPE_STRING = [
        'enum',
        'char',
        'nchar',
        'nvarchar',
        'varchar',
        'longtext',
        'mediumtext',
        'text',
        'tinytext',
        'blob',
        'varbinary'
    ];
    protected const DATA_TYPE_INT = ['int', 'bigint', 'decimal', 'double', 'float', 'mediumint', 'real', 'smallint', 'tinyint'];
    protected const DATA_TYPE_DATE_TIME = ['date', 'time', 'timestamp', 'datetime'];

	public static function getKey(string $type, string $key): ?string
	{
		return self::DATABASE_KEYWORDS[$type][$key] ?? null;
	}
}
