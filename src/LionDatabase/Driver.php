<?php 

declare(strict_types=1);

namespace LionDatabase;

class Driver
{
	public static function run(array $connections): object
	{
		if (empty($connections['default'])) {
			return (object) ['status' => 'database-error', 'message' => 'the default driver is required'];
		}

		$connection = $connections['connections'][$connections['default']];

		if ('mysql' === strtolower($connection['type'])) {
			// \LionDatabase\Drivers\MySQL\MySQL::init($connections);
			// \LionDatabase\Drivers\MySQL\Schema::init();
		} else {
			return (object) ['status' => 'database-error', 'message' => 'the driver does not exist'];
		}

		return (object) ['status' => 'success', 'message' => 'enabled connections'];
	}
}