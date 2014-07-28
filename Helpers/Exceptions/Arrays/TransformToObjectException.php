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
    public static function fieldNamesArrayNotValid(array $array)
    {
        return new self(sprintf(
            'Can\'t use array %s for field names',
            var_export($array, true)
        ));
    }
} 