<?php

namespace Internal;


class PHPCodeGenerator
{
    const THIS = '$this';
    const CLASS_ACCESSOR = '->';
    const OPEN_TAG = '<?php';
    const CLASS_DEF = 'class ';
    const RETURN_DEF = 'return';
    const NEW_DEF = 'new';
    const OPEN_BRACE = '{';
    const CLOSE_BRACE = '}';
    const SEMICOLON = ';';
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_PROTECTED = 'protected';
    const VISIBILITY_PUBLIC = 'public';
    const FUNCTION_DEF = 'function';
    const DOLLAR = '$';
    const EQUAL = '=';
    const SET = 'set';
    const GET = 'get';
    const OPEN_BRACKET = '[';
    const CLOSE_BRACKET = ']';
    const OPEN_PARENTHESIS = '(';
    const CLOSE_PARENTHESIS = ')';
    const TAB = '\t';

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

    public static function closeBracket()
    {
        return self::CLOSE_BRACKET . PHP_EOL;
    }
    public static function openBracket()
    {
        return self::OPEN_BRACKET . PHP_EOL;
    }

    public static function emptyArray()
    {
        return self::OPEN_BRACKET . self::CLOSE_BRACKET . self::SEMICOLON;
    }

    public static function property($name, $visibility = self::VISIBILITY_PRIVATE, $initValue = null)
    {
        $initValueString = self::getInitValueString($initValue);
        $initValue = $initValueString ? self::spaced(self::EQUAL) . $initValueString : '';
        return $visibility . ' ' . self::DOLLAR . $name . $initValue . self::SEMICOLON . self::doubleEol();
    }

    public static function getter($property, $visibility = self::VISIBILITY_PUBLIC)
    {
        return $visibility . self::spaced(self::FUNCTION_DEF) .
                self::GET . ucfirst($property) . self::OPEN_PARENTHESIS . self::CLOSE_PARENTHESIS .
                PHP_EOL . self::openBrace() . PHP_EOL . self::RETURN_DEF . ' ' . self::THIS .
                self::CLASS_ACCESSOR . self::mVar($property) . self::SEMICOLON . self::doubleEol() . self::closeBrace();
    }

    public static function setter($property, $visibility = self::VISIBILITY_PUBLIC, $propertyType = null, $fluent = true)
    {
        $type = $propertyType ? $propertyType . ' ': '';
        $return = $fluent ? self::RETURN_DEF . ' ' . self::THIS . self::SEMICOLON . self::doubleEol() : '';
        return $visibility . self::spaced(self::FUNCTION_DEF) .
                self::SET . ucfirst($property) . self::OPEN_PARENTHESIS . $type .
                self::DOLLAR . $property . self::CLOSE_PARENTHESIS . PHP_EOL .
                self::OPEN_BRACE . self::doubleEol() . self::THIS .
                self::CLASS_ACCESSOR . $property . self::spaced(self::EQUAL) .
                self::mVar($property) . self::SEMICOLON . self::doubleEol() . $return .
                self::closeBrace() . self::doubleEol();
    }

    public static function spaced($value)
    {
        return ' ' . $value . ' ';
    }

    public static function mVar($varName)
    {
        return self::DOLLAR . $varName;
    }
    private static function getInitValueString($initValue)
    {
        switch (true) {
            case is_object($initValue):
                $initValueString = null;
                break;
            case is_array($initValue):
                return self::emptyArray();
                break;
            default:
                $initValueString = $initValue;
                break;
        }
        return $initValueString;
    }

    private static function getNewReflector($obj)
    {
        return new \ReflectionClass($obj);
    }

    public static function doubleEol()
    {
        return PHP_EOL . PHP_EOL;
    }

    public static function mObject($obj)
    {
        return self::NEW_DEF . ' '.
        self::getNewReflector($obj)->getName().
        self::OPEN_PARENTHESIS . self::CLOSE_PARENTHESIS;
    }
} 