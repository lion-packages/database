<?php

/**
 * -----------------------------------------------------------------------------
 * Register The Auto Loader
 * -----------------------------------------------------------------------------
 * Composer provides a convenient, automatically generated class loader for this
 * application
 * -----------------------------------------------------------------------------
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Lion\Database\Driver;

/**
 * -----------------------------------------------------------------------------
 * Register environment variable loader automatically
 * -----------------------------------------------------------------------------
 * .dotenv provides an easy way to access environment variables with $_ENV
 * -----------------------------------------------------------------------------
 */

if (file_exists(__DIR__ . '/../.env')) {
    Dotenv::createMutable(__DIR__ . '/../')->load();
}

/**
 * Defining constants for MySQL
 */

const DATABASE_TYPE_MYSQL = 'mysql';
define("DATABASE_HOST_MYSQL", $_ENV['DB_HOST_MYSQL']);
const DATABASE_PORT_MYSQL = 3306;
const DATABASE_NAME_MYSQL = 'lion_database';
const DATABASE_USER_MYSQL = 'root';
define('DATABASE_PASSWORD_MYSQL', $_ENV['DB_PASSWORD_MYSQL']);
const DATABASE_NAME_SECOND_MYSQL = 'lion_database_second';
const CONNECTION_DATA_MYSQL = [
    'type' => DATABASE_TYPE_MYSQL,
    'host' => DATABASE_HOST_MYSQL,
    'port' => DATABASE_PORT_MYSQL,
    'dbname' => DATABASE_NAME_MYSQL,
    'user' => DATABASE_USER_MYSQL,
    'password' => DATABASE_PASSWORD_MYSQL,
];
const CONNECTION_DATA_SECOND_MYSQL = [
    'type' => DATABASE_TYPE_MYSQL,
    'host' => DATABASE_HOST_MYSQL,
    'port' => DATABASE_PORT_MYSQL,
    'dbname' => DATABASE_NAME_SECOND_MYSQL,
    'user' => DATABASE_USER_MYSQL,
    'password' => DATABASE_PASSWORD_MYSQL,
];
const CONNECTIONS_MYSQL = [
    'default' => DATABASE_NAME_MYSQL,
    'connections' => [
        DATABASE_NAME_MYSQL => CONNECTION_DATA_MYSQL,
    ],
];
const ADMINISTRATOR_MYSQL = 'administrator';

/**
 * Defining constants for PostgreSQL
 */

const DATABASE_TYPE_POSTGRESQL = 'postgresql';
define('DATABASE_HOST_POSTGRESQL', $_ENV['DB_HOST_POSTGRESQL']);
const DATABASE_PORT_POSTGRESQL = 5432;
const DATABASE_NAME_POSTGRESQL = 'lion_database';
const DATABASE_USER_POSTGRESQL = 'root';
define('DATABASE_PASSWORD_POSTGRESQL', $_ENV['DB_PASSWORD_POSTGRESQL']);
const DATABASE_NAME_SECOND_POSTGRESQL = 'lion_database_second';
const CONNECTION_DATA_POSTGRESQL = [
    'type' => DATABASE_TYPE_POSTGRESQL,
    'host' => DATABASE_HOST_POSTGRESQL,
    'port' => DATABASE_PORT_POSTGRESQL,
    'dbname' => DATABASE_NAME_POSTGRESQL,
    'user' => DATABASE_USER_POSTGRESQL,
    'password' => DATABASE_PASSWORD_POSTGRESQL,
];
const CONNECTION_DATA_SECOND_POSTGRESQL = [
    'type' => DATABASE_TYPE_POSTGRESQL,
    'host' => DATABASE_HOST_POSTGRESQL,
    'port' => DATABASE_PORT_POSTGRESQL,
    'dbname' => DATABASE_NAME_SECOND_POSTGRESQL,
    'user' => DATABASE_USER_POSTGRESQL,
    'password' => DATABASE_PASSWORD_POSTGRESQL,
];
const CONNECTIONS_POSTGRESQL = [
    'default' => DATABASE_NAME_POSTGRESQL,
    'connections' => [
        DATABASE_NAME_POSTGRESQL => CONNECTION_DATA_POSTGRESQL,
    ],
];

/**
 * Defining constants for ConnectionTest
 */

const DATABASE_NAME_CONNECTION = 'lion_database';
const DATABASE_NAME_SECOND_CONNECTION = 'lion_database_second';
const DATABASE_NAME_THIRD_CONNECTION = 'lion_database_third';
const CONNECTION_DATA_CONNECTION = [
    'type' => Driver::MYSQL,
    'host' => DATABASE_HOST_MYSQL,
    'port' => DATABASE_PORT_MYSQL,
    'dbname' => DATABASE_NAME_CONNECTION,
    'user' => DATABASE_USER_MYSQL,
    'password' => DATABASE_PASSWORD_MYSQL
];
const CONNECTION_DATA_SECOND_CONNECTION = [
    'type' => Driver::MYSQL,
    'host' => DATABASE_HOST_MYSQL,
    'port' => DATABASE_PORT_MYSQL,
    'dbname' => DATABASE_NAME_SECOND_CONNECTION,
    'user' => DATABASE_USER_MYSQL,
    'password' => DATABASE_PASSWORD_MYSQL
];
const CONNECTION_DATA_THIRD_CONNECTION = [
    'type' => Driver::POSTGRESQL,
    'host' => DATABASE_HOST_POSTGRESQL,
    'port' => DATABASE_PORT_POSTGRESQL,
    'dbname' => DATABASE_NAME_POSTGRESQL,
    'user' => DATABASE_USER_POSTGRESQL,
    'password' => DATABASE_PASSWORD_POSTGRESQL,
];
const CONNECTIONS_CONNECTION = [
    'default' => DATABASE_NAME_CONNECTION,
    'connections' => [
        DATABASE_NAME_CONNECTION => CONNECTION_DATA_CONNECTION,
        DATABASE_NAME_SECOND_CONNECTION => CONNECTION_DATA_SECOND_CONNECTION,
    ],
];
