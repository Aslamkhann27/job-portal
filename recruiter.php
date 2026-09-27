<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/job_repository.php';

cc_require_recruiter();
$currentUser = cc_current_user();

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requirements = preg_split('/\r\n|\r|\n/', trim($_POST['requirements'] ?? ''));

    $payload = [
        'title' => $_POST['title'] ?? '',
        'company' => $_POST['company'] ?? '',
        'location' => $_POST['location'] ?? '',
        'type' => $_POST['type'] ?? 'Full-time',
        'category' => $_POST['category'] ?? 'Development',
        'salary' => $_POST['salary'] ?? '',
        'seats' => (int)($_POST['seats'] ?? 1),
        'posted' => $_POST['posted'] ?? 'Just now',
        'description' => $_POST['description'] ?? '',
        'recruiter' => $_POST['recruiter'] ?? ($currentUser['name'] ?? 'Recruiter'),
        'skill_test_required' => isset($_POST['skill_test_required']) ? 1 : 0,
        'requirements' => $requirements
    ];

    if (trim($payload['title']) === '') {
        $errors[] = 'Job title is required.';
    }

    if (trim($payload['company']) === '') {
        $errors[] = 'Company is required.';
    }

    if (trim($payload['description']) === '') {
        $errors[] = 'Description is required.';
    }

    if ((int)$payload['seats'] < 1) {
        $errors[] = 'Seats must be at least 1.';
    }

    if (!$errors) {
        cc_create_job($payload, $currentUser);
        $successMessage = 'Job created successfully.';
    }
}

$recruiterJobs = cc_get_recruiter_jobs($currentUser);

function cc_field(array $job = null, string $key = '', string $default = ''): string
{
    if (!$job) {
        return $default;
    }

    return (string)($job[$key] ?? $default);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recruiter Portal | Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light glass-nav sticky-top">
        <div class="container d-flex justify-content-between">
            <a class="navbar-brand fw-bold" href="index.php">Recruiter Portal</a>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-dark btn-sm" href="index.php">Home</a>
                <a class="btn btn-outline-dark btn-sm" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="job-card p-4">
                        <h1 class="h4 mb-3">Create Job</h1>

                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($successMessage): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                        <?php endif; ?>

                        <form method="post" class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Job Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Company</label>
                                <input type="text" name="company" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Contract">Contract</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="Development">Development</option>
                                    <option value="Design">Design</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Analytics">Analytics</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Salary</label>
                                <input type="text" name="salary" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Number of Seats</label>
                                <input type="number" min="1" name="seats" class="form-control" value="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Posted Text</label>
                                <input type="text" name="posted" class="form-control" value="Just now">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Recruiter Name</label>
                                <input type="text" name="recruiter" class="form-control" value="<?php echo htmlspecialchars($currentUser['name'] ?? 'Recruiter'); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Requirements (one per line)</label>
                                <textarea name="requirements" class="form-control" rows="4"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="skill_test_required" name="skill_test_required">
                                    <label class="form-check-label" for="skill_test_required">
                                        Make Mini Skill Test compulsory for this job
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 d-grid">
                                <button class="btn btn-brand" type="submit">Save Job</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="job-card p-4">
                        <h2 class="h5 mb-3">Your Jobs</h2>
                        <?php if (!$recruiterJobs): ?>
                            <p class="text-muted mb-0">No jobs created yet.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($recruiterJobs as $job): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($job['title'] ?? ''); ?></h6>
                                                <p class="small text-muted mb-1"><?php echo htmlspecialchars(($job['company'] ?? '') . ' | ' . ($job['location'] ?? '')); ?></p>
                                                <p class="small mb-0">
                                                    Seats: <?php echo (int)($job['seats'] ?? 1); ?> |
                                                    Skill Test:
                                                    <?php echo !empty($job['skill_test_required']) ? 'Required' : 'Optional'; ?>
                                                </p>
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <a class="btn btn-outline-primary btn-sm" href="job.php?id=<?php echo (int)($job['id'] ?? 0); ?>">View</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
