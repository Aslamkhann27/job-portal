<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/job_repository.php';

cc_require_admin();
$user = cc_current_user();

$statusMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');

    if ($action === 'delete_job') {
        $jobId = (int)($_POST['job_id'] ?? 0);
        if ($jobId > 0) {
            $jobs = cc_load_jobs();
            $jobs = array_values(array_filter($jobs, function ($job) use ($jobId) {
                return (int)($job['id'] ?? 0) !== $jobId;
            }));
            cc_save_jobs($jobs);

            $applicationsFile = __DIR__ . '/data/applications.json';
            $applications = cc_read_json_file($applicationsFile);
            $applications = array_values(array_filter($applications, function ($application) use ($jobId) {
                return (int)($application['job_id'] ?? 0) !== $jobId;
            }));
            cc_write_json_file($applicationsFile, $applications);

            $testsFile = __DIR__ . '/data/skill_tests.json';
            $tests = cc_read_json_file($testsFile);
            $tests = array_values(array_filter($tests, function ($test) use ($jobId) {
                return (int)($test['job_id'] ?? 0) !== $jobId;
            }));
            cc_write_json_file($testsFile, $tests);

            $statusMessage = 'Job removed successfully.';
        }
    }

    if ($action === 'delete_seeker') {
        $userId = trim($_POST['user_id'] ?? '');
        if ($userId !== '') {
            $users = cc_load_users();
            $target = null;
            foreach ($users as $u) {
                if (($u['id'] ?? '') === $userId) {
                    $target = $u;
                    break;
                }
            }

            if ($target && ($target['role'] ?? '') === 'candidate') {
                $users = array_values(array_filter($users, function ($u) use ($userId) {
                    return ($u['id'] ?? '') !== $userId;
                }));
                cc_save_users($users);

                $applicationsFile = __DIR__ . '/data/applications.json';
                $applications = cc_read_json_file($applicationsFile);
                $applications = array_values(array_filter($applications, function ($application) use ($userId) {
                    return ($application['user_id'] ?? '') !== $userId;
                }));
                cc_write_json_file($applicationsFile, $applications);

                $testsFile = __DIR__ . '/data/skill_tests.json';
                $tests = cc_read_json_file($testsFile);
                $tests = array_values(array_filter($tests, function ($test) use ($userId) {
                    return ($test['user_id'] ?? '') !== $userId;
                }));
                cc_write_json_file($testsFile, $tests);

                $statusMessage = 'Seeker removed successfully.';
            }
        }
    }
}

$applicationsFile = __DIR__ . '/data/applications.json';
$applications = cc_read_json_file($applicationsFile);
$jobs = cc_load_jobs();
$users = cc_load_users();
$seekers = array_values(array_filter($users, function ($u) {
    return ($u['role'] ?? '') === 'candidate';
}));

usort($applications, function ($a, $b) {
    return strcmp($b['applied_at'] ?? '', $a['applied_at'] ?? '');
});
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light glass-nav sticky-top">
        <div class="container d-flex justify-content-between">
            <a class="navbar-brand fw-bold" href="index.php">Job Portal Admin</a>
            <div class="d-flex gap-2">
                <span class="btn btn-light btn-sm disabled">Signed in: <?php echo htmlspecialchars($user['name'] ?? 'Admin'); ?></span>
                <a class="btn btn-outline-dark btn-sm" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <?php if ($statusMessage): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($statusMessage); ?></div>
            <?php endif; ?>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h1 class="h3 mb-1">Applications Dashboard</h1>
                    <p class="text-muted mb-0">Review test applications in one place.</p>
                </div>
                <span class="badge text-bg-light border">Total Applications: <?php echo count($applications); ?></span>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="job-card p-3 p-lg-4">
                        <h2 class="h5 mb-3">Manage Jobs</h2>
                        <?php if (!$jobs): ?>
                            <p class="text-muted mb-0">No jobs available.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Job</th>
                                            <th>Seats</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($jobs as $job): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($job['title'] ?? ''); ?></td>
                                                <td><?php echo (int)($job['seats'] ?? 1); ?></td>
                                                <td>
                                                    <form method="post" onsubmit="return confirm('Delete this job?');">
                                                        <input type="hidden" name="action" value="delete_job">
                                                        <input type="hidden" name="job_id" value="<?php echo (int)($job['id'] ?? 0); ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete Job</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="job-card p-3 p-lg-4">
                        <h2 class="h5 mb-3">Manage Seekers</h2>
                        <?php if (!$seekers): ?>
                            <p class="text-muted mb-0">No seeker accounts available.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($seekers as $seeker): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($seeker['name'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($seeker['email'] ?? ''); ?></td>
                                                <td>
                                                    <form method="post" onsubmit="return confirm('Delete this seeker account?');">
                                                        <input type="hidden" name="action" value="delete_seeker">
                                                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($seeker['id'] ?? ''); ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete Seeker</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="job-card p-3 p-lg-4">
                <?php if (!$applications): ?>
                    <p class="mb-0 text-muted">No applications submitted yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Job</th>
                                    <th>Skill Test</th>
                                    <th>Resume</th>
                                    <th>Applied At</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $application): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($application['name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($application['email'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($application['job_title'] ?? ''); ?></td>
                                        <td>
                                            <?php if (isset($application['skill_test_score']) && $application['skill_test_score'] !== null): ?>
                                                <div><?php echo (int)$application['skill_test_score']; ?>%</div>
                                                <small class="text-muted"><?php echo htmlspecialchars($application['skill_test_status'] ?? ''); ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">Not Attempted</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($application['resume'])): ?>
                                                <a href="<?php echo htmlspecialchars($application['resume']); ?>" target="_blank" rel="noopener">View Resume</a>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($application['applied_at'] ?? ''); ?></td>
                                        <td style="min-width: 220px"><?php echo htmlspecialchars($application['message'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
