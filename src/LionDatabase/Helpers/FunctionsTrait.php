<?php

declare(strict_types=1);

namespace LionDatabase\Helpers;

trait FunctionsTrait
{
    use DriverTrait;
    use KeywordsTrait;

    protected static function getColumnSettings(string $sql): string
    {
        $union = '';
        $foreignIndex = '';
        $foreignConstraint = '';

        if (count(self::$schemaOptions['columns']) > 0) {
            $union .= self::addColumns(self::$schemaOptions['columns']);
        }

        if (count(self::$schemaOptions['indexes']) > 0) {
            $union .= ", " . self::addColumns(self::$schemaOptions['indexes']);
        }

        if (count(self::$schemaOptions['foreign']['index']) > 0) {
            $foreignIndex .= self::addColumns(self::$schemaOptions['foreign']['index'], false) . ";";
        }

        if (count(self::$schemaOptions['foreign']['constraint']) > 0) {
            $foreignConstraint .= self::addColumns(self::$schemaOptions['foreign']['constraint'], false)  . ";";
        }

        $newSql = str_replace("--FOREIGN_INDEX--", $foreignIndex, $sql);
        $newSql = str_replace("--FOREIGN_CONSTRAINT--", $foreignConstraint, $newSql);
        $newSql = str_replace("--COLUMN_SETTINGS--", $union, trim($newSql));

        return str_replace(", *", '', $newSql);
    }

    protected static function addColumnSettings(string $column, array $settings): void
    {
        $separateTable = explode(".", self::$table);

        if (!isset($settings['primary-key'])) {
            if (!isset($settings['foreign-key'])) {
                $column = isset($separateTable[1]) ? "{$separateTable[1]}_{$column}" : "{$separateTable[0]}_{$column}";
            } else {
                $column = $column;
            }
        } else {
            $column = isset($separateTable[1]) ? "{$column}{$separateTable[1]}" : "{$column}{$separateTable[0]}";
        }

        $strColumnSetting = $column;
        $strColumnIndexes = '';

        // columns
        if (in_array($settings['type'], self::DATA_TYPE_STRING)) {
            if ($settings['type'] === "enum") {
                $strColumnSetting .= str_replace(
                    "?",
                    self::addEnumColumns($settings['options']),
                    self::getKey('mysql', $settings['type'])
                );
            } else {
                $strColumnSetting .= str_replace(
                    "?",
                    isset($settings['lenght']) ? $settings['lenght'] : 45,
                    self::getKey('mysql', $settings['type'])
                );
            }
        }

        if (in_array($settings['type'], self::DATA_TYPE_DATE_TIME)) {
            $strColumnSetting .= self::getKey('mysql', $settings['type']);
        }

        if (in_array($settings['type'], self::DATA_TYPE_INT)) {
            $strColumnSetting .= str_replace(
                "?",
                isset($settings['lenght']) ? $settings['lenght'] : 11,
                self::getKey('mysql', $settings['type'])
            );
        }

        if (isset($settings['null'])) {
            $strColumnSetting .= !$settings['null'] ? self::getKey('mysql', 'not-null') : self::getKey('mysql', 'null');
        } else {
            $strColumnSetting .= self::getKey('mysql', 'null');
        }

        if (isset($settings['auto-increment'])) {
            if ($settings['auto-increment']) {
                $strColumnSetting .= self::getKey('mysql', 'auto-increment');
            }
        }

        if (isset($settings['default'])) {
            if ($settings['default'] != false) {
                $strColumnSetting .= self::getKey('mysql', 'default') . " '{$settings['default']}'";
            }
        } else {
            if (!isset($settings['primary-key'])) {
                $strColumnSetting .= self::getKey('mysql', 'default') . self::getKey('mysql', 'null');
            }
        }

        if (isset($settings['comment'])) {
            $strColumnSetting .= self::getKey('mysql', 'comment') . " '{$settings['comment']}'";
        }

        // Indexes
        if (isset($settings['primary-key'])) {
            if ($settings['primary-key']) {
                $strColumnIndexes .= str_replace("?", $column, self::getKey('mysql', 'primary-key'));
            }
        }

        if (isset($settings['unique'])) {
            if ($settings['unique']) {
                $strColumnIndexes .= self::getKey('mysql', 'unique') . self::getKey('mysql', 'index');
                $strColumnIndexes .= " {$column}_UNIQUE ({$column}" . self::getKey('mysql', 'asc') . ")";
            }
        }

        if (isset($settings['foreign-key'])) {
            $column_fk = " {$separateTable[0]}_{$column}_FK";

            if (count(self::$schemaOptions['foreign']['index']) > 0) {
                $foreignIndexStr = self::getKey('mysql', 'add') . self::getKey('mysql', 'index');
                $foreignIndexStr .= "{$column_fk}_idx ({$column} " . self::getKey('mysql', 'asc') . ")";

                self::$schemaOptions['foreign']['index'][] = $foreignIndexStr;
            } else {
                $foreignIndexStr = self::getKey('mysql', 'alter') . self::getKey('mysql', 'table');
                $foreignIndexStr .= " " . self::$table . self::getKey('mysql', 'add') . self::getKey('mysql', 'index');
                $foreignIndexStr .= "{$column_fk}_idx ({$column} " . self::getKey('mysql', 'asc') . ")";

                self::$schemaOptions['foreign']['index'][] = $foreignIndexStr;
            }

            if (count(self::$schemaOptions['foreign']['constraint']) > 0) {
                $foreignConstraintStr = self::getKey('mysql', 'add') . self::getKey('mysql', 'constraint');
                $foreignConstraintStr .= $column_fk . self::getKey('mysql', 'foreign') . self::getKey('mysql', 'key');
                $foreignConstraintStr .= " ({$column})" . self::getKey('mysql', 'references');
                $foreignConstraintStr .= " {$settings['foreign-key']['table']} ({$settings['foreign-key']['column']})";
                $foreignConstraintStr .= self::getKey('mysql', 'on') . self::getKey('mysql', 'delete');
                $foreignConstraintStr .= self::getKey('mysql', 'restrict') . self::getKey('mysql', 'on');
                $foreignConstraintStr .= self::getKey('mysql', 'update') . self::getKey('mysql', 'restrict');

                self::$schemaOptions['foreign']['constraint'][] = $foreignConstraintStr;
            } else {
                $foreignConstraintStr = self::getKey('mysql', 'alter') . self::getKey('mysql', 'table');
                $foreignConstraintStr .= " " . self::$table . self::getKey('mysql', 'add');
                $foreignConstraintStr .= self::getKey('mysql', 'constraint') . $column_fk;
                $foreignConstraintStr .= self::getKey('mysql', 'foreign') . self::getKey('mysql', 'key');
                $foreignConstraintStr .= " ({$column})" . self::getKey('mysql', 'references');
                $foreignConstraintStr .= " {$settings['foreign-key']['table']} ({$settings['foreign-key']['column']})";
                $foreignConstraintStr .= self::getKey('mysql', 'on') . self::getKey('mysql', 'delete');
                $foreignConstraintStr .= self::getKey('mysql', 'restrict') . self::getKey('mysql', 'on');
                $foreignConstraintStr .= self::getKey('mysql', 'update') . self::getKey('mysql', 'restrict');

                self::$schemaOptions['foreign']['constraint'][] .= $foreignConstraintStr;
            }
        }

        self::$schemaOptions['columns'][] = trim($strColumnSetting);
        self::$schemaOptions['indexes'][] = trim($strColumnIndexes);
    }

    protected static function addCharacterBulk(array $rows, bool $addQuotes = false): string
    {
        $addValues = '';
        $size = count($rows) - 1;

        foreach ($rows as $key => $rowChild) {
            $row = !self::$isSchema
                ? self::addCharacter($rowChild)
                : self::addColumns(array_values($rowChild), true, $addQuotes);

            $str = "({$row})";
            $addValues.= $key === $size ? $str : "{$str}, ";
        }

        return $addValues;
    }

    protected static function addCharacterEqualTo(array $rows): string
    {
        $addValues = '';
        $index = 0;
        $size = count($rows) - 1;

        foreach (array_keys($rows) as $key) {
            $addValues.= $index === $size ? "{$key}=?" : "{$key}=?, ";
            $index++;
        }

        return $addValues;
    }

    protected static function addCharacterAssoc(array $rows): string
    {
        $addValues = '';
        $size = count($rows) - 1;

        for ($i = 0; $i < count($rows); $i++) {
            $addValues.= $i === $size ? "?" : "?, ";
        }

        return $addValues;
    }

    protected static function addCharacter(array $rows): string
    {
        $addValues = '';
        $size = count($rows) - 1;

        foreach (array_keys($rows) as $key) {
            $addValues.= $key === $size ? "?" : "?, ";
        }

        return $addValues;
    }

    protected static function addColumns(array $columns, bool $spacing = true, bool $addQuotes = false): string
    {
        $stringColumns = '';
        $newColumns = [];

        foreach ($columns as $key => $column) {
            if (!empty(trim($column))) {
                $newColumns[] = $column;
            }
        }

        $countColumns = count($newColumns);
        $size = $countColumns - 1;

        if ($countColumns > 0) {
            foreach ($newColumns as $key => $column) {
                if (!empty($column)) {
                    if (self::$isSchema && self::$enableInsert && $addQuotes) {
                        $stringColumns.= $key === $size
                            ? "'{$column}'"
                            : (!$spacing ? "'{$column}'," : "'{$column}', ");
                    } else {
                        $stringColumns.= $key === $size ? "{$column}" : (!$spacing ? "{$column}," : "{$column}, ");
                    }
                }
            }
        } else {
            $stringColumns = '*';
        }

        return $stringColumns;
    }

    protected static function addEnumColumns(array $columns, bool $spacing = true): string
    {
        $stringColumns = '';
        $newColumns = self::cleanSettings($columns);
        $countColumns = count($newColumns);
        $size = $countColumns - 1;

        if ($countColumns > 0) {
            foreach ($newColumns as $key => $column) {
                if (!empty($column)) {
                    $stringColumns.= $key === $size ? "'{$column}'" : (!$spacing ? "'{$column}'," : "'{$column}', ");
                }
            }
        } else {
            $stringColumns = "*";
        }

        return $stringColumns;
    }
}
