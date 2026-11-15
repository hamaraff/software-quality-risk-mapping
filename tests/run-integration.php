<?php

require_once __DIR__ . '/integration/AppointementsPersistenceTest.php';

$dsn = getenv('TEST_DATABASE_DSN');
if ($dsn === false || $dsn === '') {
    fwrite(STDERR, "TEST_DATABASE_DSN must point to a dedicated test database.\n");
    exit(2);
}

if (!preg_match('/(?:^|;)dbname=([^;]+)/i', $dsn, $matches) || stripos($matches[1], 'test') === false) {
    fwrite(STDERR, "Refusing to run integration tests: the database name must contain 'test'.\n");
    exit(2);
}

try {
    $db = new PDO($dsn, getenv('TEST_DATABASE_USER') ?: '', getenv('TEST_DATABASE_PASSWORD') ?: '', array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ));
} catch (PDOException $error) {
    fwrite(STDERR, 'Unable to connect to the test database: ' . $error->getMessage() . PHP_EOL);
    exit(2);
}

$tests = appointementsPersistenceTests();
$failures = 0;

foreach ($tests as $name => $test) {
    try {
        $db->beginTransaction();
        $test($db);
        $db->rollBack();
        echo '[PASS] ' . $name . PHP_EOL;
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $failures++;
        fwrite(STDERR, '[FAIL] ' . $name . ': ' . $error->getMessage() . PHP_EOL);
    }
}

echo count($tests) . ' test(s), ' . $failures . ' failure(s).' . PHP_EOL;
exit($failures === 0 ? 0 : 1);
