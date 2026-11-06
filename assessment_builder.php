<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/job_repository.php';
require_once __DIR__ . '/includes/skill_tests.php';

cc_require_recruiter();
$currentUser = cc_current_user();
$jobId = (int)($_GET['id'] ?? $_POST['job_id'] ?? 0);
$job = $jobId > 0 ? cc_get_job_by_id($jobId) : null;

if (!$job || (string)($job['created_by'] ?? '') !== (string)($currentUser['id'] ?? '')) {
    http_response_code($job ? 403 : 404);
    exit($job ? 'You can only manage assessments for jobs you created.' : 'Job not found.');
}

if (empty($_SESSION['assessment_csrf'])) {
    $_SESSION['assessment_csrf'] = bin2hex(random_bytes(32));
}

$errors = [];
$successMessage = '';
$questions = cc_get_questions_for_job($job);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals((string)$_SESSION['assessment_csrf'], $token)) {
        $errors[] = 'Your session expired. Refresh the page and try again.';
    } else {
        $submitted = is_array($_POST['questions'] ?? null) ? $_POST['questions'] : [];
        $questions = array_values($submitted);
        $validated = [];

        for ($index = 0; $index < 5; $index++) {
            $item = $submitted[$index] ?? [];
            if (!is_array($item)) {
                $item = [];
            }
            $questionText = trim((string)($item['question'] ?? ''));
            $rawOptions = is_array($item['options'] ?? null) ? $item['options'] : [];
            $options = array_map('trim', $rawOptions);
            $answer = filter_var($item['answer'] ?? null, FILTER_VALIDATE_INT);

            if ($questionText === '') {
                $errors[] = 'Question ' . ($index + 1) . ' is required.';
            }
            if (count($options) !== 4 || in_array('', $options, true)) {
                $errors[] = 'Question ' . ($index + 1) . ' must have four non-empty options.';
            }
            if ($answer === false || $answer < 0 || $answer > 3) {
                $errors[] = 'Choose the correct option for question ' . ($index + 1) . '.';
            }

            $validated[] = [
                'question' => $questionText,
                'options' => array_values($options),
                'answer' => $answer === false ? -1 : $answer
            ];
        }

        if (!$errors && cc_update_job_assessment($jobId, $validated, $currentUser)) {
            $questions = $validated;
            $successMessage = 'Assessment saved. Candidates must complete it before applying.';
        } elseif (!$errors) {
            $errors[] = 'The assessment could not be saved. Check that you still own this job.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Build Assessment | Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light glass-nav">
    <div class="container">
        <a class="navbar-brand fw-bold" href="recruiter.php">Recruiter Portal</a>
        <a class="btn btn-outline-dark btn-sm" href="recruiter.php">Back to jobs</a>
    </div>
</nav>
<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="job-card p-4 p-lg-5">
                    <div class="mb-4">
                        <span class="badge text-bg-primary mb-2">Candidate assessment</span>
                        <h1 class="h3 mb-1">Build test: <?php echo htmlspecialchars($job['title']); ?></h1>
                        <p class="text-muted mb-0">Create five questions with four options each. Select the correct answer for automatic scoring.</p>
                    </div>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?><div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div><?php endif; ?>
                    <form method="post">
                        <input type="hidden" name="job_id" value="<?php echo (int)$jobId; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['assessment_csrf']); ?>">
                        <?php for ($index = 0; $index < 5; $index++): ?>
                            <?php $question = $questions[$index] ?? ['question' => '', 'options' => ['', '', '', ''], 'answer' => 0]; ?>
                            <section class="border rounded-3 p-3 mb-3">
                                <h2 class="h6 mb-3">Question <?php echo $index + 1; ?></h2>
                                <label class="form-label" for="question_<?php echo $index; ?>">Question text</label>
                                <input class="form-control mb-3" id="question_<?php echo $index; ?>" name="questions[<?php echo $index; ?>][question]" maxlength="500" value="<?php echo htmlspecialchars($question['question'] ?? ''); ?>" required>
                                <div class="row g-2">
                                    <?php for ($option = 0; $option < 4; $option++): ?>
                                        <div class="col-md-6">
                                            <label class="form-label" for="q_<?php echo $index; ?>_option_<?php echo $option; ?>">Option <?php echo $option + 1; ?></label>
                                            <input class="form-control" id="q_<?php echo $index; ?>_option_<?php echo $option; ?>" name="questions[<?php echo $index; ?>][options][]" maxlength="250" value="<?php echo htmlspecialchars(is_array($question['options'] ?? null) ? ($question['options'][$option] ?? '') : ''); ?>" required>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                <label class="form-label mt-3" for="answer_<?php echo $index; ?>">Correct answer</label>
                                <select class="form-select" id="answer_<?php echo $index; ?>" name="questions[<?php echo $index; ?>][answer]" required>
                                    <?php for ($option = 0; $option < 4; $option++): ?>
                                        <option value="<?php echo $option; ?>" <?php echo (int)($question['answer'] ?? 0) === $option ? 'selected' : ''; ?>>Option <?php echo $option + 1; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </section>
                        <?php endfor; ?>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-brand" type="submit">Save assessment</button>
                            <a class="btn btn-outline-secondary" href="job.php?id=<?php echo (int)$jobId; ?>">Preview job</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
