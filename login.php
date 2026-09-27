<?php
require_once __DIR__ . '/includes/auth.php';

cc_start_session();

if (cc_is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$email = '';
$next = cc_sanitize_next($_GET['next'] ?? ($_POST['next'] ?? 'index.php'), 'index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (!$errors) {
        $user = cc_authenticate_user($email, $password);
        if ($user) {
            cc_login_user($user);
            header('Location: ' . $next);
            exit;
        }

        $errors[] = 'Invalid email or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="py-5 min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-5">
                    <div class="job-card p-4 p-lg-5">
                        <h1 class="h3 mb-3">Login</h1>
                        <p class="text-muted mb-4">Enter your account details to continue.</p>

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
                            <input type="hidden" name="next" value="<?php echo htmlspecialchars($next); ?>">
                            <div class="col-12">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-brand">Login</button>
                            </div>
                        </form>

                        <p class="mt-3 mb-0">New user? <a href="register.php">Create account</a>.</p>
                        <a href="index.php" class="btn btn-link ps-0 mt-2">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
