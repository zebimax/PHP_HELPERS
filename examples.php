<?php
use Helpers\Refactor\Arrays\TransformToObject;

require_once 'autoLoad.php';
$testArrays = [
    [
        'testA' => 1, 'testB' => 2, 'testTest_test_c-34-%%' => 3, '%%__S' => 4, '\'^&*' => 5
    ],
    [],
    []
];
$test = new TransformToObject($testArrays[0]);
$test->generateObjectClass();