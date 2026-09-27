<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/skill_tests.php';
require_once __DIR__ . '/includes/job_repository.php';

cc_start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$currentUser = cc_current_user();
if (!$currentUser) {
    $next = 'index.php';
    if (isset($_POST['job_id'])) {
        $next = 'job.php?id=' . (int)$_POST['job_id'] . '#apply';
    }
    header('Location: login.php?next=' . urlencode($next));
    exit;
}

$jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$resume = trim($_POST['resume'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];

if ($name === '') {
    $errors[] = 'Name is required.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email is required.';
}

if (!filter_var($resume, FILTER_VALIDATE_URL)) {
    $errors[] = 'A valid resume URL is required.';
}

if ($message === '') {
    $errors[] = 'Cover note is required.';
}

if (strtolower($email) !== strtolower($currentUser['email'] ?? '')) {
    $errors[] = 'Application email must match your logged-in account.';
}

$selectedJob = cc_get_job_by_id($jobId);

if (!$selectedJob) {
    $errors[] = 'Selected job was not found.';
}

$latestSkillResult = cc_get_latest_skill_test_result($currentUser['id'] ?? '', $jobId);
$isSkillRequired = (bool)($selectedJob['skill_test_required'] ?? false);

if ($isSkillRequired && !$latestSkillResult) {
    $errors[] = 'Mini Skill Test is required for this job before applying.';
}

$seatsTotal = max(1, (int)($selectedJob['seats'] ?? 1));
$applicationsForJob = cc_read_json_file(__DIR__ . '/data/applications.json');
$appliedCount = 0;
foreach ($applicationsForJob as $existingApplication) {
    if ((int)($existingApplication['job_id'] ?? 0) === $jobId) {
        $appliedCount++;
    }
}

if ($appliedCount >= $seatsTotal) {
    $errors[] = 'No seats left for this job.';
}

if (!$errors) {
    $storageDir = __DIR__ . '/data';
    $storageFile = $storageDir . '/applications.json';

    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0777, true);
    }

    $applications = [];
    if (file_exists($storageFile)) {
        $json = file_get_contents($storageFile);
        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            $applications = $decoded;
        }
    }

    $applications[] = [
        'job_id' => $jobId,
        'job_title' => $selectedJob['title'],
        'user_id' => $currentUser['id'] ?? null,
        'account_email' => $currentUser['email'] ?? null,
        'name' => $name,
        'email' => $email,
        'resume' => $resume,
        'message' => $message,
        'skill_test_score' => $latestSkillResult['score'] ?? null,
        'skill_test_status' => $latestSkillResult['status'] ?? 'Not Attempted',
        'skill_test_submitted_at' => $latestSkillResult['submitted_at'] ?? null,
        'applied_at' => date('c')
    ];

    file_put_contents($storageFile, json_encode($applications, JSON_PRETTY_PRINT));
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Status | Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="py-5 min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="job-card p-4 p-lg-5">
                        <?php if ($errors): ?>
                            <h1 class="h3 text-danger mb-3">Application Failed</h1>
                            <p class="text-muted">Please correct the details below and try again.</p>
                            <ul class="mb-4">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="javascript:history.back()" class="btn btn-outline-dark">Go Back</a>
                        <?php else: ?>
                            <h1 class="h3 text-success mb-3">Application Submitted</h1>
                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                            <p class="mb-3"><strong>Job:</strong> <?php echo htmlspecialchars($selectedJob['title']); ?></p>
                            <p class="text-muted">Thanks for applying. Your application has been saved successfully.</p>
                            <a href="index.php" class="btn btn-brand mt-2">Back to Home</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
