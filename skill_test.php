<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/skill_tests.php';
require_once __DIR__ . '/includes/job_repository.php';

$jobId = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['job_id'] ?? 0);
if ($jobId <= 0) {
    header('Location: index.php');
    exit;
}

cc_require_login('skill_test.php?id=' . $jobId);
$currentUser = cc_current_user();

$selectedJob = cc_get_job_by_id($jobId);

if (!$selectedJob) {
    http_response_code(404);
}

$questions = $selectedJob ? cc_get_questions_for_job($selectedJob) : [];
$latestResult = $selectedJob && $currentUser ? cc_get_latest_skill_test_result($currentUser['id'], $jobId) : null;
$evaluation = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedJob && $currentUser) {
    $answers = [];
    foreach ($questions as $index => $question) {
        $key = 'q_' . $index;
        if (isset($_POST[$key])) {
            $answers[$index] = (int)$_POST[$key];
        }
    }

    $evaluation = cc_evaluate_skill_test($questions, $answers);

    $attempt = [
        'attempt_id' => uniqid('test_', true),
        'user_id' => $currentUser['id'],
        'user_name' => $currentUser['name'],
        'user_email' => $currentUser['email'],
        'job_id' => $jobId,
        'job_title' => $selectedJob['title'],
        'total' => $evaluation['total'],
        'correct' => $evaluation['correct'],
        'score' => $evaluation['score'],
        'status' => $evaluation['status'],
        'submitted_at' => date('c')
    ];

    cc_save_skill_test_attempt($attempt);
    $latestResult = $attempt;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mini Skill Test | Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light glass-nav sticky-top">
        <div class="container d-flex justify-content-between">
            <a class="navbar-brand fw-bold" href="index.php">Job Portal</a>
            <div class="d-flex gap-2">
                <?php if (cc_is_recruiter()): ?>
                    <a class="btn btn-outline-dark btn-sm" href="recruiter.php">Recruiter</a>
                <?php endif; ?>
                <a class="btn btn-outline-dark btn-sm" href="job.php?id=<?php echo (int)$jobId; ?>#apply">Back to Job</a>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <?php if (!$selectedJob): ?>
                <div class="alert alert-danger">Job not found.</div>
            <?php else: ?>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="job-card p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <h1 class="h4 mb-0">Mini Skill Test - <?php echo htmlspecialchars($selectedJob['title']); ?></h1>
                                <span class="badge text-bg-light border" id="testTimer" data-seconds="300">Time Left: 05:00</span>
                            </div>
                            <p class="text-muted">Answer all questions. Your score is saved automatically and visible to admin with your application.</p>

                            <?php if ($evaluation): ?>
                                <div class="alert <?php echo $evaluation['score'] >= 60 ? 'alert-success' : 'alert-warning'; ?>">
                                    <strong>Score:</strong> <?php echo (int)$evaluation['score']; ?>% 
                                    (<?php echo (int)$evaluation['correct']; ?>/<?php echo (int)$evaluation['total']; ?>) - 
                                    <?php echo htmlspecialchars($evaluation['status']); ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" id="skillTestForm" class="row g-3">
                                <input type="hidden" name="job_id" value="<?php echo (int)$jobId; ?>">
                                <?php foreach ($questions as $index => $question): ?>
                                    <div class="col-12">
                                        <div class="border rounded p-3">
                                            <p class="mb-2 fw-semibold"><?php echo ($index + 1) . '. ' . htmlspecialchars($question['question']); ?></p>
                                            <?php foreach ($question['options'] as $optionIndex => $option): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="q_<?php echo (int)$index; ?>" id="q_<?php echo (int)$index; ?>_<?php echo (int)$optionIndex; ?>" value="<?php echo (int)$optionIndex; ?>" required>
                                                    <label class="form-check-label" for="q_<?php echo (int)$index; ?>_<?php echo (int)$optionIndex; ?>">
                                                        <?php echo htmlspecialchars($option); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="col-12 d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-brand">Submit Test</button>
                                    <a href="job.php?id=<?php echo (int)$jobId; ?>#apply" class="btn btn-outline-dark">Go to Apply Section</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="job-card p-4">
                            <h2 class="h6">Latest Result</h2>
                            <?php if ($latestResult): ?>
                                <p class="mb-1"><strong>Score:</strong> <?php echo (int)($latestResult['score'] ?? 0); ?>%</p>
                                <p class="mb-1"><strong>Status:</strong> <?php echo htmlspecialchars($latestResult['status'] ?? '-'); ?></p>
                                <p class="mb-0 text-muted small"><strong>Submitted:</strong> <?php echo htmlspecialchars($latestResult['submitted_at'] ?? ''); ?></p>
                            <?php else: ?>
                                <p class="text-muted mb-0">No test attempt found yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="assets/js/skill-test.js"></script>
</body>
</html>
