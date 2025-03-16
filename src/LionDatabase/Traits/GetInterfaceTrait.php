<?php

declare(strict_types=1);

namespace Lion\Database\Traits;

use Lion\Database\Interface\DatabaseCapsuleInterface;
use PDOException;
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
    public static function get(): array|DatabaseCapsuleInterface|stdClass
    {
        return parent::process(function (): array|DatabaseCapsuleInterface|stdClass {
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

                if (!self::$stmt->execute()) {
                    throw new PDOException(self::$stmt->errorInfo()[2], 500);
                }

                $data = self::$stmt->fetch();

                if (count(self::$fetchMode) > 1) {
                    $responses[] = !$data ? [] : $data;
                } else {
                    $responses = !$data ? [] : $data;
                }
            }

            return $responses;
        });
    }
}
