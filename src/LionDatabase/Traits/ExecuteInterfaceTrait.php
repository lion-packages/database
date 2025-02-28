<?php

declare(strict_types=1);

namespace Lion\Database\Traits;

use Lion\Database\Interface\DatabaseCapsuleInterface;
use PDOException;
use stdClass;

/**
 * Declare the execute method of the interface
 *
 * @package Lion\Database\Traits
 */
trait ExecuteInterfaceTrait
{
    /**
     * {@inheritdoc}
     */
    public static function execute(): int|stdClass
    {
        $method = self::$databaseMethod;

        return parent::{$method}(function (): array|DatabaseCapsuleInterface|int|stdClass {
            $dataInfoKeys = array_keys(self::$dataInfo);

            if (count($dataInfoKeys) > 0) {
                self::$listSql = array_map(
                    fn ($value) => trim($value),
                    array_filter(explode(';', trim(self::$sql)), fn ($value) => trim($value) != '')
                );

                foreach ($dataInfoKeys as $key => $code) {
                    self::prepare(self::$listSql[$key]);

                    if (!empty(self::$dataInfo[$code])) {
                        self::bindValue($code);
                    }

                    if (!self::$stmt->execute()) {
                        throw new PDOException(self::$stmt->errorInfo()[2], 500);
                    }

                    self::$stmt->closeCursor();
                }
            } else {
                self::prepare(self::$sql);

                if (!empty(self::$actualCode)) {
                    self::bindValue(self::$actualCode);
                }

                if (!self::$stmt->execute()) {
                    throw new PDOException(self::$stmt->errorInfo()[2], 500);
                }
            }

            if (self::$withRowCount) {
                return self::$stmt->rowCount();
            }

            return (object) [
                'code' => 200,
                'status' => 'success',
                'message' => self::$message,
            ];
        });
    }
}
