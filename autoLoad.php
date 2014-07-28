<?php
spl_autoload_register('autoLoad');
function autoLoad($className)
{
    include_once __DIR__ . transformToPath($className);
}

function transformToPath($className)
{
    return DIRECTORY_SEPARATOR .
        str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
}