<?php

declare(strict_types=1);

namespace Lion\Database\Helpers;

use stdClass;

/**
 * Declare the execute method of the interface
 *
 * @package Lion\Database\Helpers
 */
trait ExecuteInterfaceTrait
{
    /**
     * {@inheritdoc}
     */
    public static function execute(): stdClass
    {
        $method = self::$databaseMethod;

        return parent::{$method}(function (): stdClass {
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

                    self::$stmt->execute();

                    self::$stmt->closeCursor();
                }
            } else {
                self::prepare(self::$sql);

                if (!empty(self::$actualCode)) {
                    self::bindValue(self::$actualCode);
                }

                self::$stmt->execute();
            }

            return (object) [
                'code' => 200,
                'status' => 'success',
                'message' => self::$message,
            ];
        });
    }
}
