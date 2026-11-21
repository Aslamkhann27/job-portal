<?php

/**
 * Check whether a candidate already has an application for a job.
 *
 * Older application records without a user_id are ignored because they
 * cannot be safely associated with a specific signed-in account.
 */
function cc_user_has_applied_to_job(array $applications, string $userId, int $jobId): bool
{
    if ($userId === '' || $jobId <= 0) {
        return false;
    }

    foreach ($applications as $application) {
        if (
            (string)($application['user_id'] ?? '') === $userId
            && (int)($application['job_id'] ?? 0) === $jobId
        ) {
            return true;
        }
    }

    return false;
}


function cc_application_statuses(): array
{
    return ['Pending', 'Under Review', 'Shortlisted', 'Rejected', 'Hired'];
}

function cc_is_valid_application_status(string $status): bool
{
    return in_array($status, cc_application_statuses(), true);
}

function cc_update_application_status(array &$applications, string $applicationId, string $status): bool
{
    if ($applicationId === '' || !cc_is_valid_application_status($status)) {
        return false;
    }

    foreach ($applications as &$application) {
        if (is_array($application) && hash_equals((string)($application['application_id'] ?? ''), $applicationId)) {
            $application['status'] = $status;
            $application['status_updated_at'] = date('c');
            unset($application);
            return true;
        }
    }
    unset($application);

    return false;
}
