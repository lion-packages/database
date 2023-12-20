<?php 

declare(strict_types=1);

namespace LionDatabase\Helpers;

use LionDatabase\Helpers\Constants\MySQLConstantsTrait;

trait KeywordsTrait
{
	use MySQLConstantsTrait;

    private const DATABASE_KEYWORDS = [
        'mysql' => self::MYSQL_KEYWORDS
    ];
    const DATA_TYPE_STRING = [
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
    const DATA_TYPE_INT = ['int', 'bigint', 'decimal', 'double', 'float', 'mediumint', 'real', 'smallint', 'tinyint'];
    const DATA_TYPE_DATE_TIME = ['date', 'time', 'timestamp', 'datetime'];

	public static function getKey(string $type, string $key): ?string
	{
		return self::DATABASE_KEYWORDS[$type][$key] ?? null;
	}
}
