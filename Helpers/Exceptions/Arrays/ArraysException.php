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
} 