<?php

require_once __DIR__ . '/unit/AppointmentPolicyTest.php';

$tests = appointmentPolicyTests();
$failures = 0;

foreach ($tests as $name => $test) {
    try {
        $test();
        echo "[PASS] " . $name . PHP_EOL;
    } catch (Throwable $error) {
        $failures++;
        fwrite(STDERR, "[FAIL] " . $name . ': ' . $error->getMessage() . PHP_EOL);
    }
}

echo count($tests) . ' test(s), ' . $failures . ' failure(s).' . PHP_EOL;
exit($failures === 0 ? 0 : 1);
