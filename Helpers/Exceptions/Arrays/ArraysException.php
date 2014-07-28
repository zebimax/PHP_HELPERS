<?php

namespace Helpers\Exceptions\Arrays;


use Helpers\Exceptions\HelpersException;

class ArraysException extends HelpersException
{
    public static function arrayNotValid(array $array, $className, $operation) {
        return new self(sprintf(
            'Array %s not valid in class %s for operation %s',
            var_export($array, true), $className, $operation
        ));
    }

    public static function dirForGenerateNotExists($dirName) {
        return new self(sprintf(
            'Dir %s for generate not exists and can\'t be created(%s)',
            $dirName, error_get_last()
        ));
    }
} 