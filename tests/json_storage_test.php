<?php
require_once __DIR__ . '/../includes/auth.php';

function assert_same($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

$directory = sys_get_temp_dir() . '/job-portal-json-' . bin2hex(random_bytes(6));
$file = $directory . '/records.json';

try {
    assert_same(true, cc_write_json_file($file, [['id' => 1, 'name' => 'Candidate']]), 'A new JSON file should be written.');
    assert_same(
        [['id' => 1, 'name' => 'Candidate']],
        cc_read_json_file($file),
        'Written JSON should round-trip without changing its structure.'
    );

    $originalContents = file_get_contents($file);
    $invalidUtf8 = ["name" => "\xB1\x31"];

    assert_same(false, cc_write_json_file($file, $invalidUtf8), 'Values that cannot be JSON-encoded should fail safely.');
    assert_same(
        $originalContents,
        file_get_contents($file),
        'A failed serialization must leave the previous data file untouched.'
    );

    $entries = array_values(array_diff(scandir($directory), ['.', '..']));
    assert_same(['records.json'], $entries, 'Temporary files must be cleaned up after a failed write.');

    echo "JSON storage tests passed." . PHP_EOL;
} finally {
    if (is_file($file)) {
        unlink($file);
    }
    if (is_dir($directory)) {
        rmdir($directory);
    }
}
