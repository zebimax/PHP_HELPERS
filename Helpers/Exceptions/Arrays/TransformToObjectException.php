<?php
/**
 * Created by PhpStorm.
 * User: ajax
 * Date: 7/28/14
 * Time: 5:53 PM
 */

namespace Helpers\Exceptions\Arrays;


class TransformToObjectException extends ArraysException
{
    public static function fieldNamesArrayNotValid(array $fields, array $array)
    {
        return new self(sprintf(
            'Can\'t use array %s for field names for array %s',
            var_export($fields, true), var_export($array, true)
        ));
    }
} 