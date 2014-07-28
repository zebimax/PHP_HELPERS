<?php
namespace Helpers\Exceptions;

class HelpersException extends \Exception
{
    public static function optionNotFound($option, $className)
    {
        return new self(sprintf(
            'No option found named %s for class %s',
            $option, $className
        ));
    }

    public static function failOpenFile($fileName, $operation)
    {
        $error_get_last = error_get_last();
        return new self(sprintf(
            'Can\'t open file %s for operation %s(%s)',
            $fileName, $operation, $error_get_last[0]
        ));
    }
} 