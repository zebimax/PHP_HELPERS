<?php

namespace Internal;


class PHPCodeGenerator
{
    const OPEN_TAG = '<?php';
    const CLASS_DEF = 'class ';
    const OPEN_BRACE = '{';

    public static function openTag()
    {
        return self::OPEN_TAG . PHP_EOL;
    }

    public static function classDefinition($className)
    {
        return self::CLASS_DEF . $className . PHP_EOL ;
    }

    public static function openBrace()
    {
        return self::OPEN_BRACE . PHP_EOL ;
    }
} 