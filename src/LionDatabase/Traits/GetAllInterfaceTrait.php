<?php

declare(strict_types=1);

namespace Lion\Database\Traits;

use stdClass;

/**
 * Declare the getAll method of the interface
 *
 * @package Lion\Database\Traits
 */
trait GetAllInterfaceTrait
{
    /**
     * {@inheritdoc}
     */
    public static function getAll(): stdClass|array
    {
        $method = self::$databaseMethod;

        return parent::{$method}(function (): stdClass|array {
            $responses = [];

            self::$listSql = array_map(
                fn ($value) => trim($value),
                array_filter(explode(';', trim(self::$sql)), fn ($value) => trim($value) != '')
            );

            $codes = array_keys(self::$fetchMode);

            foreach (self::$listSql as $key => $sql) {
                self::prepare($sql);

                $code = $codes[$key] ?? null;

                if ($code != null && isset(self::$dataInfo[$code])) {
                    self::bindValue($code);
                }

                if ($code != null && isset(self::$fetchMode[$code])) {
                    $get_fetch = self::$fetchMode[$codes[$key]];

                    if (is_array($get_fetch)) {
                        self::$stmt->setFetchMode($get_fetch[0], $get_fetch[1]);
                    } else {
                        self::$stmt->setFetchMode(self::$fetchMode[$codes[$key]]);
                    }
                }

                self::$stmt->execute();

                $request = self::$stmt->fetchAll();

                if (!$request) {
                    if (count(self::$fetchMode) > 1) {
                        $responses[] = (object) [
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'no data available',
                        ];
                    } else {
                        $responses = (object) [
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'no data available',
                        ];
                    }
                } else {
                    if (count(self::$fetchMode) > 1) {
                        $responses[] = $request;
                    } else {
                        $responses = $request;
                    }
                }
            }

            return $responses;
        });
    }
}
