<?php
use Helpers\Refactor\Arrays\TransformToObject;

require_once 'autoLoad.php';
$testArrays = [
    [
        'testA_TEST___' => 1, '_testBad_' => 2, 'testTest_test_c-34-%%' => 3, '__s$$' => 4, '5_a_%_5___5___sdf' => 5
    ],
    [],
    []
];
$test = new TransformToObject($testArrays[0]);
$test->generateObjectClass();