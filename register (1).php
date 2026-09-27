<?php
require_once __DIR__ . '/includes/auth.php';

cc_start_session();

if (cc_is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = false;
$name = '';
$email = '';
$roleChoice = 'candidate';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $roleChoice = trim($_POST['role_choice'] ?? 'candidate');

    if ($name === '') {
        $errors[] = 'Name is required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $role = 'candidate';
        if ($roleChoice === 'recruiter') {
            $role = 'recruiter';
        }

        if (!$errors) {
            $result = cc_register_user($name, $email, $password, $role);

            if ($result['success']) {
                cc_login_user($result['user']);
                header('Location: index.php');
                exit;
            }

            $errors[] = $result['message'];
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="py-5 min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="job-card p-4 p-lg-5">
                        <h1 class="h3 mb-3">Create Account</h1>
                        <p class="text-muted mb-4">Register once and apply to jobs easily.</p>

                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="name">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="confirm_password">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="role_choice">Account Type</label>
                                <select class="form-select" id="role_choice" name="role_choice">
                                    <option value="candidate" <?php echo $roleChoice === 'candidate' ? 'selected' : ''; ?>>Candidate</option>
                                    <option value="recruiter" <?php echo $roleChoice === 'recruiter' ? 'selected' : ''; ?>>Recruiter</option>
                                </select>
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-brand">Register</button>
                            </div>
                        </form>

                        <p class="mt-3 mb-0">Already have an account? <a href="login.php">Login</a>.</p>
                        <a href="index.php" class="btn btn-link ps-0 mt-2">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
