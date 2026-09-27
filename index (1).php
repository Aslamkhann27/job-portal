<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/job_repository.php';

cc_start_session();
$currentUser = cc_current_user();
$jobs = cc_load_jobs();
$companies = [];
foreach ($jobs as $job) {
    $company = trim((string)($job['company'] ?? ''));
    if ($company !== '') {
        $companies[$company] = true;
    }
}
$jobCount = count($jobs);
$companyCount = count($companies);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="bg-aurora"></div>

    <nav class="navbar navbar-expand-lg navbar-light glass-nav sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Job Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#jobs">Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="#stats">Overview</a></li>
                    <?php if ($currentUser): ?>
                        <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
                        <?php endif; ?>
                        <?php if (cc_is_recruiter()): ?>
                            <li class="nav-item"><a class="nav-link" href="recruiter.php">Recruiter Portal</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><span class="nav-link">Hi, <?php echo htmlspecialchars($currentUser['name']); ?></span></li>
                        <li class="nav-item"><a class="btn btn-outline-dark ms-lg-2" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="btn btn-brand ms-lg-2" href="#jobs">Find Jobs</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero-section py-5">
        <div class="container">
            <div class="alert alert-info" role="alert">
                Job portal application.
            </div>
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="badge badge-pill-custom mb-3">Project Version</span>
                    <h1 class="display-6 fw-bold mb-3">Job Portal</h1>
                    <p class="text-muted mb-4">Browse jobs, register/login, and submit applications through a complete workflow.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="#jobs" class="btn btn-brand">View Jobs</a>
                        <a href="#stats" class="btn btn-outline-dark">Portal Info</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero-card p-4">
                        <h5 class="mb-3">Project Notes</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Type</span>
                            <strong>Academic Project</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Storage</span>
                            <strong>JSON files</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Auth</span>
                            <strong>Session based</strong>
                        </div>
                        <hr>
                            <p class="small text-muted mb-0">Tip: Customize data and design according to your project requirements.</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section id="stats" class="pb-4">
            <div class="container">
                <div class="row g-3 text-center">
                    <div class="col-6 col-lg-3">
                        <div class="stat-card">
                            <h2 class="counter" data-target="<?php echo (int)$jobCount; ?>">0</h2>
                            <p>Listed Jobs</p>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="stat-card">
                            <h2 class="counter" data-target="<?php echo (int)$companyCount; ?>">0</h2>
                            <p>Companies</p>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="stat-card">
                            <h2 class="counter" data-target="900">0</h2>
                            <p>Test Applications</p>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="stat-card">
                            <h2 class="counter" data-target="85">0</h2>
                            <p>Success Rate %</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="jobs" class="py-5">
            <div class="container">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <h2 class="mb-1">Job Openings</h2>
                        <p class="text-muted mb-0">Filter by title, location, or category.</p>
                    </div>
                    <span class="badge text-bg-light border" id="jobCount">Showing <?php echo (int)$jobCount; ?> jobs</span>
                </div>

                <form class="row g-3 mb-4" id="filterForm" onsubmit="return false;">
                    <div class="col-md-5">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search title or company...">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="locationInput" placeholder="Location">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="categorySelect">
                            <option value="">All Categories</option>
                            <option value="Development">Development</option>
                            <option value="Design">Design</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Analytics">Analytics</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button class="btn btn-dark" type="button" id="clearFilters">Clear</button>
                    </div>
                </form>

                <div class="row g-4" id="jobGrid">
                    <?php foreach ($jobs as $job): ?>
                        <div class="col-md-6 col-xl-4 job-item"
                             data-title="<?php echo htmlspecialchars($job['title']); ?>"
                             data-company="<?php echo htmlspecialchars($job['company']); ?>"
                             data-location="<?php echo htmlspecialchars($job['location']); ?>"
                             data-category="<?php echo htmlspecialchars($job['category']); ?>">
                            <div class="job-card h-100">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge text-bg-warning-subtle border border-warning-subtle text-warning-emphasis"><?php echo htmlspecialchars($job['type']); ?></span>
                                    <small class="text-muted"><?php echo htmlspecialchars($job['posted']); ?></small>
                                </div>
                                <h5><?php echo htmlspecialchars($job['title']); ?></h5>
                                <p class="mb-1 fw-semibold"><?php echo htmlspecialchars($job['company']); ?></p>
                                <p class="text-muted small mb-3"><?php echo htmlspecialchars($job['location']); ?> | <?php echo htmlspecialchars($job['salary']); ?></p>
                                <p class="small mb-2"><?php echo htmlspecialchars($job['description']); ?></p>
                                <p class="small text-muted mb-1">Recruiter: <?php echo htmlspecialchars($job['recruiter'] ?? 'HR Team'); ?></p>
                                <p class="small text-muted mb-4">Seats: <?php echo (int)($job['seats'] ?? 1); ?> | Skill Test: <?php echo !empty($job['skill_test_required']) ? 'Required' : 'Optional'; ?></p>
                                <div class="d-flex gap-2 mt-auto">
                                    <a href="job.php?id=<?php echo (int)$job['id']; ?>" class="btn btn-outline-dark btn-sm">View Details</a>
                                    <a href="job.php?id=<?php echo (int)$job['id']; ?>#apply" class="btn btn-brand btn-sm">Apply Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <p class="text-muted mt-3 d-none" id="emptyState">No jobs match your filters. Try different keywords.</p>
            </div>
        </section>
    </main>

    <footer class="py-4 border-top bg-white">
        <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Job Portal.</p>
            <p class="mb-0 text-muted">Built with PHP, Bootstrap, JavaScript, HTML and CSS.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
