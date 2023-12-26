<?php 

declare(strict_types=1);

namespace LionDatabase;

use LionDatabase\Drivers\MySQL;

abstract class Driver
{
	public static function run(array $connections): object
	{
		if (empty($connections['default'])) {
			return (object) ['status' => 'database-error', 'message' => 'the default driver is required'];
		}

		$connection = $connections['connections'][$connections['default']];
        $type = trim(strtolower($connection['type']));

        switch ($type) {
            case 'mysql':
            MySQL::run($connections);
            break;

            default:
            return (object) ['status' => 'database-error', 'message' => 'the driver does not exist'];
            break;
        }

        return (object) ['status' => 'success', 'message' => 'enabled connections'];
    }
}
