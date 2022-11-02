<?php

namespace LionSQL;

class Keywords {

	protected static array $keywords = [
		'in' => " IN(?)",
		'where' => " WHERE",
		'as' => " AS",
		'and' => " AND",
		'or' => " OR",
		'between' => " BETWEEN",
		'select' => " SELECT",
		'from' => " FROM",
		'join' => " JOIN",
		'on' => " ON",
		'left' => " LEFT",
		'right' => " RIGHT",
		'inner' => " INNER",
		'insert' => " INSERT INTO",
		'values' => " VALUES",
		'update' => " UPDATE",
		'set' => " SET",
		'delete' => " DELETE",
		'call' => " CALL",
		'like' => " LIKE",
		'groupBy' => ' GROUP BY',
		'asc' => ' ASC',
		'desc' => ' DESC',
		'orderBy' => ' ORDER BY',
		'count' => ' COUNT(?)',
		'max' => ' MAX(?)',
		'min' => ' MIN(?)',
		'sum' => ' SUM(?)',
		'avg' => ' AVG(?)',
		'limit' => ' LIMIT',
		'having' => ' HAVING',
		'show' => ' SHOW',
		'tables' => ' TABLES',
		'columns' => ' COLUMNS'
	];

	public function __construct() {

	}

	protected static function addCharacterEqualTo(array $rows): string {
		$addValues = "";
		$index = 0;
		$indexSize = count($rows) - 1;

		foreach ($rows as $key => $row) {
			$addValues.= $index === $indexSize ? "{$key}=?" : "{$key}=?, ";
			$index++;
		}

		return $addValues;
	}

	protected static function addCharacterAssoc(array $rows): string {
		$addValues = "";
		$indexSize = count($rows) - 1;

		for ($i = 0; $i < count($rows); $i++) {
			$addValues.= $i === $indexSize ? "?" : "?, ";
		}

		return $addValues;
	}

	protected static function addCharacter(array $rows): string {
		$addValues = "";
		$indexSize = count($rows) - 1;

		foreach ($rows as $key => $file) {
			$addValues.= $key === $indexSize ? "?" : "?, ";
		}

		return $addValues;
	}

	protected static function addColumns(array $columns): string {
		$stringColumns = "";
		$countColumns = count($columns);
		$indexSize = $countColumns - 1;

		if ($countColumns > 0) {
			foreach ($columns as $key => $column) {
				$stringColumns.= $key === $indexSize ? "{$column}" : "{$column}, ";
			}
		} else {
			$stringColumns = "*";
		}

		return $stringColumns;
	}

}