<?php

function cc_jobs_file_path(): string
{
    return __DIR__ . '/../data/jobs.json';
}

function cc_seed_jobs_data_if_missing(): void
{
    $filePath = cc_jobs_file_path();
    if (file_exists($filePath)) {
        return;
    }

    require __DIR__ . '/jobs.php';

    $seedJobs = [];
    foreach ($jobs as $job) {
        $job['skill_test_required'] = (bool)($job['skill_test_required'] ?? false);
        $job['seats'] = isset($job['seats']) ? (int)$job['seats'] : 1;
        $job['created_by'] = $job['created_by'] ?? null;
        $job['created_by_name'] = $job['created_by_name'] ?? ($job['recruiter'] ?? 'HR Team');
        $seedJobs[] = $job;
    }

    cc_write_json_file($filePath, $seedJobs);
}

function cc_load_jobs(): array
{
    cc_seed_jobs_data_if_missing();
    $jobs = cc_read_json_file(cc_jobs_file_path());

    usort($jobs, function ($a, $b) {
        return (int)($a['id'] ?? 0) <=> (int)($b['id'] ?? 0);
    });

    return $jobs;
}

function cc_save_jobs(array $jobs): bool
{
    return cc_write_json_file(cc_jobs_file_path(), $jobs);
}

function cc_get_job_by_id(int $jobId): ?array
{
    foreach (cc_load_jobs() as $job) {
        if ((int)($job['id'] ?? 0) === $jobId) {
            return $job;
        }
    }

    return null;
}

function cc_next_job_id(array $jobs): int
{
    $max = 0;
    foreach ($jobs as $job) {
        $max = max($max, (int)($job['id'] ?? 0));
    }

    return $max + 1;
}

function cc_upsert_job(array $payload, array $currentUser): array
{
    $jobs = cc_load_jobs();
    $jobId = isset($payload['id']) ? (int)$payload['id'] : 0;
    $now = date('Y-m-d H:i:s');

    $data = [
        'title' => trim($payload['title'] ?? ''),
        'company' => trim($payload['company'] ?? ''),
        'location' => trim($payload['location'] ?? ''),
        'type' => trim($payload['type'] ?? 'Full-time'),
        'category' => trim($payload['category'] ?? 'Development'),
        'salary' => trim($payload['salary'] ?? ''),
        'seats' => max(1, (int)($payload['seats'] ?? 1)),
        'posted' => trim($payload['posted'] ?? 'Just now'),
        'description' => trim($payload['description'] ?? ''),
        'recruiter' => trim($payload['recruiter'] ?? ($currentUser['name'] ?? 'Recruiter')),
        'skill_test_required' => !empty($payload['skill_test_required']),
        'requirements' => array_values(array_filter(array_map('trim', $payload['requirements'] ?? []), function ($line) {
            return $line !== '';
        })),
        'created_by' => $currentUser['id'] ?? null,
        'created_by_name' => $currentUser['name'] ?? 'Recruiter',
        'updated_at' => $now
    ];

    if ($jobId > 0) {
        foreach ($jobs as $index => $job) {
            if ((int)($job['id'] ?? 0) === $jobId) {
                $jobs[$index] = array_merge($job, $data);
                cc_save_jobs($jobs);
                return $jobs[$index];
            }
        }
    }

    $data['id'] = cc_next_job_id($jobs);
    $jobs[] = $data;
    cc_save_jobs($jobs);

    return $data;
}

function cc_create_job(array $payload, array $currentUser): array
{
    $payload['id'] = 0;
    return cc_upsert_job($payload, $currentUser);
}

function cc_get_recruiter_jobs(array $currentUser): array
{
    $allJobs = cc_load_jobs();
    if (($currentUser['role'] ?? '') === 'admin') {
        return $allJobs;
    }

    $userId = $currentUser['id'] ?? '';
    return array_values(array_filter($allJobs, function ($job) use ($userId) {
        return ($job['created_by'] ?? '') === $userId;
    }));
}
