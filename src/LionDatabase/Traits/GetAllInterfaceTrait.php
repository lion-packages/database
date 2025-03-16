<?php

declare(strict_types=1);

namespace Lion\Database\Traits;

use Lion\Database\Interface\DatabaseCapsuleInterface;
use PDOException;
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
    public static function getAll(): array|stdClass
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
                    $get_fetch = self::$fetchMode[$codes[$key]];

                    if (is_array($get_fetch)) {
                        self::$stmt->setFetchMode($get_fetch[0], $get_fetch[1]);
                    } else {
                        self::$stmt->setFetchMode(self::$fetchMode[$codes[$key]]);
                    }
                }

                if (!self::$stmt->execute()) {
                    throw new PDOException(self::$stmt->errorInfo()[2], 500);
                }

                if (count(self::$fetchMode) > 1) {
                    $responses[] = self::$stmt->fetchAll();
                } else {
                    $responses = self::$stmt->fetchAll();
                }
            }

            return $responses;
        });
    }
}
