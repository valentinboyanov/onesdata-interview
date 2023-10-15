<?php 

declare(strict_types=1);

require_once "acme.php";

$testsFunctions = [];
$allFunctions = get_defined_functions();

foreach ($allFunctions["user"] as $function) {
    if (str_starts_with($function, "test_")) {
        $testsFunctions[] = $function;
    }
}

foreach ($testsFunctions as $test) {
    try {
        $test();
        echo "Test '{$test}' OK!" . PHP_EOL;
    } catch (Throwable $t) {
        echo "Test '{$test}' FAILED!" . PHP_EOL;
        echo "{$t}" . PHP_EOL;
        echo PHP_EOL;
    }
}
