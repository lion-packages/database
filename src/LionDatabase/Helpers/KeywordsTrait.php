<?php 

declare(strict_types=1);

namespace Lion\Database\Helpers;

use Lion\Database\Driver;
use Lion\Database\Helpers\Constants\MySQLConstants;

trait KeywordsTrait
{
    private const DATABASE_KEYWORDS = [
        Driver::MYSQL => MySQLConstants::KEYWORDS
    ];

	public static function getKey(string $type, string $key): ?string
	{
		return self::DATABASE_KEYWORDS[$type][$key] ?? null;
	}
}
