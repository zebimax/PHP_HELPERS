<?php

namespace Internal;


class PHPCodeGenerator
{
    const OPEN_TAG = '<?php';
    const CLASS_DEF = 'class ';
    const OPEN_BRACE = '{';
    const CLOSE_BRACE = '}';
    const SEMICOLON = ';';
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_PROTECTED = 'protected';
    const VISIBILITY_PUBLIC = 'public';
    const DOLLAR = '$';
    const EQUAL = ' = ';


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

    public static function closeBrace()
    {
        return self::CLOSE_BRACE . PHP_EOL ;
    }

    public static function property($name, $visibility = self::VISIBILITY_PRIVATE, $initValue = null)
    {
        $initValue = $initValue ? self::EQUAL . $initValue : '';
        return $visibility . ' ' . self::DOLLAR . $name . $initValue . self::SEMICOLON . PHP_EOL;
    }
} 