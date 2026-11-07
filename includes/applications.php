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
