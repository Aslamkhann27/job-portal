<?php
require_once __DIR__ . '/../includes/applications.php';

function assert_same($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

$applications = [
    ['user_id' => 'candidate-1', 'job_id' => 12],
    ['user_id' => 'candidate-2', 'job_id' => 12],
    ['user_id' => 'candidate-1', 'job_id' => 15],
    ['job_id' => 12]
];

assert_same(
    true,
    cc_user_has_applied_to_job($applications, 'candidate-1', 12),
    'A candidate should be recognized as having already applied to the same job.'
);
assert_same(
    false,
    cc_user_has_applied_to_job($applications, 'candidate-2', 15),
    'An application to another job must not block this application.'
);
assert_same(
    false,
    cc_user_has_applied_to_job($applications, 'candidate-3', 12),
    'Another candidate must be allowed to apply to the same job.'
);
assert_same(
    false,
    cc_user_has_applied_to_job($applications, '', 12),
    'An empty user ID must not match legacy records.'
);
assert_same(
    false,
    cc_user_has_applied_to_job($applications, 'candidate-1', 0),
    'An invalid job ID must not match an application.'
);

echo "Application validation tests passed." . PHP_EOL;
