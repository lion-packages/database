#!/bin/bash

echo -e "\n\033[0;36m\t>>  Set Time Zone \033[0m"
export TZ=America/Bogota
echo -e "\033[0;36m\t>>  America/Bogota \033[0m"
start_time=$(date +"%Y-%m-%d %H:%M:%S")
echo -e "\n\033[0;31m>> -------------------------------------------------------------------------------------- << \n\033[0m";

echo -e "\033[0;36m\t>>  Install Dependencies \033[0m"
rm -rf vendor/
composer install
echo -e "\n\033[0;31m>> -------------------------------------------------------------------------------------- << \n\033[0m";

echo -e "\033[0;36m\t>>  Dump Autoload \033[0m"
composer dump-autoload
echo -e "\n\033[0;31m>> -------------------------------------------------------------------------------------- << \n\033[0m";

echo -e "\033[0;36m\t>>  Helpers \033[0m"
php vendor/bin/phpunit --testsuite Helpers
echo -e "\n\033[0;31m>> -------------------------------------------------------------------------------------- << \n\033[0m";

echo -e "\033[0;36m\t>>  Connection|Driver \033[0m"
php vendor/bin/phpunit --testsuite "Connection|Driver"
echo -e "\n\033[0;31m>> -------------------------------------------------------------------------------------- << \n\033[0m";

# echo -e "\033[0;36m\t>>  Drivers|Schema \033[0m"
# php vendor/bin/phpunit --testsuite Drivers
# echo -e "\n\033[0;31m>> -------------------------------------------------------------------------------------- << \n\033[0m";

echo -e "\033[0;36m\t>>  Drivers \033[0m"
php vendor/bin/phpunit tests/Drivers/MySQLTest.php
echo -e "\n\033[0;31m>> -------------------------------------------------------------------------------------- << \n\033[0m";

echo -e "\033[0;36m\t>>  Schema \033[0m"
php vendor/bin/phpunit tests/Drivers/Schema/MySQLTest.php
echo -e "\n\033[0;31m>> -------------------------------------------------------------------------------------- << \n\033[0m";

end_time=$(date +"%Y-%m-%d %H:%M:%S")
start_seconds=$(date -d "$start_time" +%s)
end_seconds=$(date -d "$end_time" +%s)
time_diff=$((end_seconds - start_seconds))
minutes=$((time_diff / 60))
seconds=$((time_diff % 60))

echo -e "\033[0;36m\t>>  Start date and time: ${start_time} \033[0m"
echo -e "\033[0;36m\t>>  End date and time: ${end_time} \033[0m"
echo -e "\033[0;32m\t>>  Time execution: ${minutes} minutes ${seconds} seconds \n \033[0m"
