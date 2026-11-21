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

$applicationRecords = [
    ['application_id' => 'app-1', 'status' => 'Pending'],
    ['application_id' => 'app-2', 'status' => 'Shortlisted']
];
assert_same(true, cc_update_application_status($applicationRecords, 'app-1', 'Under Review'), 'A known application should accept a valid status update.');
assert_same('Under Review', $applicationRecords[0]['status'], 'The application status should be updated.');
assert_same(false, cc_update_application_status($applicationRecords, 'app-1', 'Unknown'), 'Unknown statuses must be rejected.');
assert_same(false, cc_update_application_status($applicationRecords, 'missing', 'Hired'), 'Unknown application IDs must not update records.');
assert_same(false, cc_is_valid_application_status(''), 'An empty status must be invalid.');

echo "Application validation tests passed." . PHP_EOL;
