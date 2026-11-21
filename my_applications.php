<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/applications.php';

cc_start_session();
$currentUser = cc_current_user();
if (!$currentUser || ($currentUser['role'] ?? '') !== 'candidate') {
    if (!$currentUser) {
        header('Location: login.php?next=' . urlencode('my_applications.php'));
        exit;
    }
    http_response_code(403);
    exit('Candidate access required.');
}

$applications = cc_read_json_file(__DIR__ . '/data/applications.json');
$myApplications = array_values(array_filter($applications, function ($application) use ($currentUser) {
    return is_array($application) && (string)($application['user_id'] ?? '') === (string)($currentUser['id'] ?? '');
}));
usort($myApplications, function ($a, $b) {
    return strcmp((string)($b['applied_at'] ?? ''), (string)($a['applied_at'] ?? ''));
});

function cc_status_badge_class(string $status): string
{
    switch ($status) {
        case 'Shortlisted':
        case 'Hired': return 'text-bg-success';
        case 'Rejected': return 'text-bg-danger';
        case 'Under Review': return 'text-bg-primary';
        default: return 'text-bg-secondary';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Applications | Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-light glass-nav">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Job Portal</a>
            <div class="d-flex gap-2 align-items-center">
                <span class="small text-muted">Hi, <?php echo htmlspecialchars($currentUser['name'] ?? 'Candidate'); ?></span>
                <a class="btn btn-outline-dark btn-sm" href="index.php">Browse Jobs</a>
                <a class="btn btn-outline-dark btn-sm" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    <main class="py-5">
        <div class="container">
            <div class="mb-4">
                <h1 class="h3 mb-1">My Applications</h1>
                <p class="text-muted mb-0">Track the latest status of every job you have applied for.</p>
            </div>
            <?php if (!$myApplications): ?>
                <div class="job-card p-4">
                    <h2 class="h5">No applications yet</h2>
                    <p class="text-muted">When you apply for a job, it will appear here.</p>
                    <a class="btn btn-brand" href="index.php#jobs">Explore jobs</a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($myApplications as $application): ?>
                        <?php $status = (string)($application['status'] ?? 'Pending'); ?>
                        <div class="col-12">
                            <article class="job-card p-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                    <div>
                                        <h2 class="h5 mb-1"><?php echo htmlspecialchars($application['job_title'] ?? 'Job application'); ?></h2>
                                        <p class="small text-muted mb-2">Applied: <?php echo htmlspecialchars($application['applied_at'] ?? 'Date unavailable'); ?></p>
                                    </div>
                                    <span class="badge <?php echo cc_status_badge_class($status); ?>"><?php echo htmlspecialchars($status); ?></span>
                                </div>
                                <?php if (!empty($application['status_updated_at'])): ?>
                                    <p class="small text-muted mb-0">Status updated: <?php echo htmlspecialchars($application['status_updated_at']); ?></p>
                                <?php endif; ?>
                                <?php if (isset($application['skill_test_score']) && $application['skill_test_score'] !== null): ?>
                                    <p class="small mb-0 mt-2">Skill test score: <?php echo (int)$application['skill_test_score']; ?>%</p>
                                <?php endif; ?>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
