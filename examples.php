<?php
use Helpers\Refactor\Arrays\TransformToObject;

require_once 'autoLoad.php';
$date = new DateTime();
$testArrays = [
    [
        'testA_TEST___' => [
            TransformToObject::CONFIG_VALUES_AS_DEFAULT => true,
            TransformToObject::CONFIG_DEFAULT_PROPERTY_VALUE => true,
            TransformToObject::CONFIG_ARRAY_VALUES_AS_DEFAULT => true,
            TransformToObject::CONFIG_FLUENT => true,
            TransformToObject::CONFIG_GENERATE_GETTERS => false,
            TransformToObject::CONFIG_SETTERS_VISIBILITY => \Internal\PHPCodeGenerator::VISIBILITY_PROTECTED,
            TransformToObject::CONFIG_DEFAULT_PROPERTY_VALUE => $date
        ]
        , '_testBad_' => 2, 'testTest_test_c-34-%%' => 3, '__s$$' => 4
    ],
    [],
    []
];
$test = new TransformToObject(
    $testArrays[0],
    [
        TransformToObject::CONFIG_FILE_FOR_GENERATE => 'Custom.php',
        TransformToObject::CONFIG_CLASS_NAME_FOR_GENERATE => 'Custom'
    ]
);
$test->generateObjectClass();