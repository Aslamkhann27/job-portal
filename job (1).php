<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/skill_tests.php';
require_once __DIR__ . '/includes/job_repository.php';

cc_start_session();
$currentUser = cc_current_user();

$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selectedJob = null;
$latestSkillResult = null;
$isSkillTestRequired = false;
$seatsTotal = 0;
$appliedCount = 0;
$seatsLeft = 0;

$selectedJob = cc_get_job_by_id($jobId);

if (!$selectedJob) {
    http_response_code(404);
}

if ($selectedJob && $currentUser) {
    $latestSkillResult = cc_get_latest_skill_test_result($currentUser['id'], $selectedJob['id']);
}

if ($selectedJob) {
    $isSkillTestRequired = (bool)($selectedJob['skill_test_required'] ?? false);
    $seatsTotal = max(1, (int)($selectedJob['seats'] ?? 1));

    $applications = cc_read_json_file(__DIR__ . '/data/applications.json');
    foreach ($applications as $application) {
        if ((int)($application['job_id'] ?? 0) === (int)$selectedJob['id']) {
            $appliedCount++;
        }
    }

    $seatsLeft = max(0, $seatsTotal - $appliedCount);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $selectedJob ? htmlspecialchars($selectedJob['title']) : 'Job Not Found'; ?> | Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light glass-nav sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Job Portal</a>
            <div class="d-flex gap-2">
                <?php if ($currentUser): ?>
                    <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                        <a class="btn btn-light btn-sm" href="admin.php">Admin</a>
                    <?php endif; ?>
                    <?php if (cc_is_recruiter()): ?>
                        <a class="btn btn-light btn-sm" href="recruiter.php">Recruiter</a>
                    <?php endif; ?>
                    <a class="btn btn-outline-dark btn-sm" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="btn btn-outline-dark btn-sm" href="login.php?next=<?php echo urlencode('job.php?id=' . (int)$jobId . '#apply'); ?>">Login</a>
                    <a class="btn btn-outline-dark btn-sm" href="register.php">Register</a>
                <?php endif; ?>
                <a class="btn btn-outline-dark btn-sm" href="index.php#jobs">Back to Jobs</a>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <?php if (!$selectedJob): ?>
                <div class="alert alert-danger">Job not found. Please go back and select another role.</div>
            <?php else: ?>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="job-card p-4 h-100">
                            <span class="badge text-bg-warning-subtle border border-warning-subtle text-warning-emphasis mb-2"><?php echo htmlspecialchars($selectedJob['type']); ?></span>
                            <h1 class="h3 mb-2"><?php echo htmlspecialchars($selectedJob['title']); ?></h1>
                            <p class="mb-1 fw-semibold"><?php echo htmlspecialchars($selectedJob['company']); ?></p>
                            <p class="text-muted"><?php echo htmlspecialchars($selectedJob['location']); ?> | <?php echo htmlspecialchars($selectedJob['salary']); ?></p>
                            <p class="text-muted mb-1">Seats: <?php echo (int)$seatsTotal; ?> | Applied: <?php echo (int)$appliedCount; ?> | Left: <?php echo (int)$seatsLeft; ?></p>
                            <p class="text-muted">Recruiter: <?php echo htmlspecialchars($selectedJob['recruiter'] ?? 'HR Team'); ?></p>
                            <hr>
                            <h2 class="h5">Job Overview</h2>
                            <p><?php echo htmlspecialchars($selectedJob['description']); ?></p>
                            <h3 class="h6 mt-4">Requirements</h3>
                            <ul>
                                <?php foreach ($selectedJob['requirements'] as $requirement): ?>
                                    <li><?php echo htmlspecialchars($requirement); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4" id="apply">
                        <div class="job-card p-4">
                            <h2 class="h5 mb-3">Mini Skill Test</h2>
                            <p class="text-muted small">Take a quick 5-question skill test related to this role.</p>
                            <p class="small mb-2">
                                <strong>Test Policy:</strong>
                                <?php if ($isSkillTestRequired): ?>
                                    <span class="text-danger">Required before applying</span>
                                <?php else: ?>
                                    <span class="text-success">Optional</span>
                                <?php endif; ?>
                            </p>
                            <div class="d-grid gap-2 mb-4">
                                <?php if ($currentUser): ?>
                                    <a class="btn btn-outline-primary" href="skill_test.php?id=<?php echo (int)$selectedJob['id']; ?>">Start Mini Skill Test</a>
                                    <?php if ($latestSkillResult): ?>
                                        <div class="small text-muted">
                                            Latest Score: <strong><?php echo (int)($latestSkillResult['score'] ?? 0); ?>%</strong>
                                            (<?php echo htmlspecialchars($latestSkillResult['status'] ?? '-'); ?>)
                                        </div>
                                    <?php elseif ($isSkillTestRequired): ?>
                                        <div class="small text-danger">This job requires at least one skill test attempt before application.</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a class="btn btn-outline-primary" href="login.php?next=<?php echo urlencode('skill_test.php?id=' . (int)$selectedJob['id']); ?>">Login to Take Skill Test</a>
                                <?php endif; ?>
                            </div>

                            <h2 class="h5 mb-3">Apply for this role</h2>
                            <?php if (!$currentUser): ?>
                                <div class="alert alert-info mb-3">Please login or register before applying.</div>
                                <div class="d-grid gap-2">
                                    <a class="btn btn-brand" href="login.php?next=<?php echo urlencode('job.php?id=' . (int)$selectedJob['id'] . '#apply'); ?>">Login to Apply</a>
                                    <a class="btn btn-outline-dark" href="register.php">Create Account</a>
                                </div>
                            <?php elseif ($isSkillTestRequired && !$latestSkillResult): ?>
                                <div class="alert alert-warning mb-3">Complete the Mini Skill Test first, then you can submit your application.</div>
                                <div class="d-grid">
                                    <a class="btn btn-outline-primary" href="skill_test.php?id=<?php echo (int)$selectedJob['id']; ?>">Take Skill Test Now</a>
                                </div>
                            <?php elseif ($seatsLeft <= 0): ?>
                                <div class="alert alert-danger mb-3">All seats are filled for this job.</div>
                            <?php else: ?>
                                <form action="apply.php" method="post" class="row g-3">
                                    <input type="hidden" name="job_id" value="<?php echo (int)$selectedJob['id']; ?>">
                                    <input type="hidden" name="job_title" value="<?php echo htmlspecialchars($selectedJob['title']); ?>">
                                    <div class="col-12">
                                        <label class="form-label" for="name">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($currentUser['name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="resume">Resume URL</label>
                                        <input type="url" class="form-control" id="resume" name="resume" placeholder="https://your-portfolio-or-resume-link" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="message">Cover Note</label>
                                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-brand">Submit Application</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
