<?php

declare(strict_types=1);

namespace Lion\Database\Traits;

use Lion\Database\Interface\DatabaseCapsuleInterface;
use stdClass;

/**
 * Declare the get method of the interface
 *
 * @package Lion\Database\Traits
 */
trait GetInterfaceTrait
{
    /**
     * {@inheritdoc}
     */
    public static function get(): stdClass|array|DatabaseCapsuleInterface
    {
        $method = self::$databaseMethod;

        return parent::{$method}(function (): stdClass|array|DatabaseCapsuleInterface {
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
                    $getFetch = self::$fetchMode[$codes[$key]];

                    if (is_array($getFetch)) {
                        self::$stmt->setFetchMode($getFetch[0], $getFetch[1]);
                    } else {
                        self::$stmt->setFetchMode(self::$fetchMode[$codes[$key]]);
                    }
                }

                self::$stmt->execute();

                $request = self::$stmt->fetch();

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
