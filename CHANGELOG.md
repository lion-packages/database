# Release Notes

## [v8.9.0](https://github.com/Lion-Packages/database/compare/v8.8.2...v8.9.0) (2023-08-08)

### Changed
- validation has been modified to read data from multiple queries

## [v8.8.2](https://github.com/Lion-Packages/database/compare/v8.8.1...v8.8.2) (2023-07-15)

### Fixed
- deprecated function has been corrected by an alternate one in the current version of php (8.2.7)

## [v8.8.1](https://github.com/Lion-Packages/database/compare/v8.8.0...v8.8.1) (2023-07-13)

### Changed
- docker config updated

### Fixed
- fixed format to generate sql query for LionDatabase\Functions
- added fetchMode initialization for functions

### Refactoring
- renamed innerJoin, leftJoin and rightJoin functions to join

## [v8.8.0](https://github.com/Lion-Packages/database/compare/v8.7.2...v8.8.0) (2023-07-11)

### Changed
- configuration in composer has been updated

## [v8.7.2](https://github.com/Lion-Packages/database/compare/v8.7.1...v8.7.2) (2023-06-25)

### Fixed
- statement to create tables in LionSQL\Drivers\MySQL\Schema has been corrected

## [v8.7.1](https://github.com/Lion-Packages/database/compare/v8.7.0...v8.7.1) (2023-06-24)

### Fixed
- reading of information to add values to the executed sentence has been corrected

## [v8.7.0](https://github.com/Lion-Packages/database/compare/v8.6.0...v8.7.0) (2023-06-19)

### Added
- function has been added to add connections

## [v8.6.0](https://github.com/Lion-Packages/database/compare/v8.5.0...v8.6.0) (2023-06-18)

### Added
- validation has been added to execute multiple sql statements
- configuration to execute transactions with execute function has been added
- added logic for multiple queries with get and getAll
- varBinary function has been added in the LionSQL\Drivers\MySQL\Schema class
- added support for importing columns with hexadecimal format
- the scope of the addRows function has been changed to public of the LionSQL\Functions class

## [v8.5.0](https://github.com/Lion-Packages/database/compare/v8.4.0...v8.5.0) (2023-06-11)

### Added
- added create function in LionSQL\Drivers\MySQL\MySQL class

### Refactoring
- refactored table method in LionSQL\Driver\MySQL\MySQL class
- refactored view method in LionSQL\Driver\MySQL\MySQL class

## [v8.4.0](https://github.com/Lion-Packages/database/compare/v8.3.0...v8.4.0) (2023-06-10)

### Added
- procedure function has been added to the LionSQL\Drivers\MySQL\MySQL class
- status function has been added to the LionSQL\Drivers\MySQL\MySQL class

## [v8.3.0](https://github.com/Lion-Packages/database/compare/v8.2.0...v8.3.0) (2023-06-08)

### Added
- added from function in LionSQL\Drivers\MySQL\MySQL class
- added schema function in LionSQL\Drivers\MySQL\Schema class
- added procedure function in LionSQL\Drivers\MySQL\Schema class
- added end function in LionSQL\Drivers\MySQL\Schema class
- added in function in LionSQL\Drivers\MySQL\Schema class
- added int function in LionSQL\Drivers\MySQL\Schema class
- added bigInt function in LionSQL\Drivers\MySQL\Schema class
- added decimal function in LionSQL\Drivers\MySQL\Schema class
- added double function in LionSQL\Drivers\MySQL\Schema class
- added float function in LionSQL\Drivers\MySQL\Schema class
- added mediumInt function in LionSQL\Drivers\MySQL\Schema class
- added real function in LionSQL\Drivers\MySQL\Schema class
- added smallInt function in LionSQL\Drivers\MySQL\Schema class
- added tinyInt function in LionSQL\Drivers\MySQL\Schema class
- added blob function in LionSQL\Drivers\MySQL\Schema class
- added char function in LionSQL\Drivers\MySQL\Schema class
- added json function in LionSQL\Drivers\MySQL\Schema class
- added nchar function in LionSQL\Drivers\MySQL\Schema class
- added nvarchar function in LionSQL\Drivers\MySQL\Schema class
- added varchar function in LionSQL\Drivers\MySQL\Schema class
- added longText function in LionSQL\Drivers\MySQL\Schema class
- added mediumText function in LionSQL\Drivers\MySQL\Schema class
- added text function in LionSQL\Drivers\MySQL\Schema class
- added tinyText function in LionSQL\Drivers\MySQL\Schema class
- added enum function in LionSQL\Drivers\MySQL\Schema class
- added date function in LionSQL\Drivers\MySQL\Schema class
- added time function in LionSQL\Drivers\MySQL\Schema class
- added timeStamp function in LionSQL\Drivers\MySQL\Schema class
- added dateTime function in LionSQL\Drivers\MySQL\Schema class
- added groupQueryBegin function in LionSQL\Drivers\MySQL\Schema class
- added groupQueryParams function in LionSQL\Drivers\MySQL\Schema class
- added groupQuery function in LionSQL\Drivers\MySQL\Schema class

### Changed
- cleaned up query when generating sql with getQueryString to avoid nesting previous queries
- added property into to generate insert

### Refactoring
- the columns and tabs function has been refactored to nest the current statement in the LionSQL\Drivers\MySQL\MySQL class

## [v8.2.0](https://github.com/Lion-Packages/database/compare/v8.1.1...v8.2.0) (2023-06-06)

### Added
- full function has been added for the mysql service

## [v8.1.1](https://github.com/Lion-Packages/database/compare/v8.1.0...v8.1.1) (2023-06-02)

### Fixed
- validation in get and getAll function is corrected

## [v8.1.0](https://github.com/Lion-Packages/database/compare/v8.0.3...v8.1.0) (2023-06-01)

### Added
- function has been added to add with to the current statement and to add sub queries and replace them
- function has been added to add recursive to the current sentence
- function has been added to group queries by means of subqueries

### Changed
- removed lion/request library

## [v8.0.3](https://github.com/Lion-Packages/database/compare/v8.0.2...v8.0.3) (2023-05-09)

### Fixed
- fixed nesting of query in exception

## [v8.0.2](https://github.com/Lion-Packages/database/compare/v8.0.1...v8.0.2) (2023-05-05)

### Fixed
- union function format has been corrected
- unionAll function format fixed

## [v8.0.1](https://github.com/Lion-Packages/database/compare/v8.0.0...v8.0.1) (2023-05-05)

### Changed
- function or has been modified to be able to add a single parameter

### Fixed
- bug for reading constraints has been fixed

## [v8.0.0](https://github.com/Lion-Packages/database/compare/v7.6.0...v8.0.0) (2023-05-04)

### Added
- schema class has been added to dynamically create tables with array parameters

### Changed
- driver class has been relocated
- MySQL driver namespace has been modified
- function and has been modified to be able to add a single parameter

## [v7.6.0](https://github.com/Lion-Packages/database/compare/v7.5.0...v7.6.0) (2023-04-16)

### Added
- added function to change the fetchMode

## [v7.5.0](https://github.com/Lion-Packages/database/compare/v7.4.0...v7.5.0) (2023-04-10)

### Added
- getConnection method was added to obtain the complete list of multiple connections

## [v7.4.0](https://github.com/Lion-Packages/database/compare/v7.3.0...v7.4.0) (2023-04-09)

### Added
- function has been added to connect multiple connections to mysql databases

## [v7.3.0](https://github.com/Lion-Packages/database/compare/v7.2.1...v7.3.0) (2023-04-05)

### Added
- added offset function for MySQL driver

## [v7.2.1](https://github.com/Lion-Packages/database/compare/v7.2.0...v7.2.1) (2023-03-30)

### Fixed
- fixed incorrect logger call

## [v7.2.0](https://github.com/Lion-Packages/database/compare/v7.1.0...v7.2.0) (2023-03-30)

### Added
- function was added to execute logger function

## [v7.1.0](https://github.com/Lion-Packages/database/compare/v7.0.0...v7.1.0) (2023-03-25)

### Added
- union function added
- unionAll function has been added
- function select Distinct has been added

### Fixed
- fixed non-existent answers