<?php
require_once __DIR__ . '/../src/Config/init.php';

if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($auth->login($username, $password)) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}

// Check for password reset success message
$successMessage = '';
if (isset($_SESSION['reset_success'])) {
    $successMessage = 'Password berhasil direset! Silakan login dengan password baru Anda.';
    unset($_SESSION['reset_success']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Academic Cash System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="auth-bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card">
                    <div class="text-center mb-4">
                        <div class="icon-box bg-primary text-white mx-auto mb-3"
                            style="width: 70px; height: 70px; border-radius: 50%; font-size: 2rem;">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <h3 class="fw-bold">Silahkan Login</h3>
                        <p class="text-muted">Sign in to Akademik Kas Sistem</p>
                    </div>

                    <?php if ($successMessage): ?>
                        <div class="alert alert-success py-2 text-center border-0 bg-success-subtle text-success">
                            <i class="bi bi-check-circle me-2"></i> <?= $successMessage ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 text-center border-0 bg-danger-subtle text-danger">
                            <i class="bi bi-exclamation-circle me-2"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-muted small">USERNAME</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i
                                        class="bi bi-person text-muted"></i></span>
                                <input type="text" name="username" class="form-control border-start-0 ps-0"
                                    placeholder="masukan username" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-muted small">PASSWORD</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i
                                        class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0"
                                    placeholder="masukan password" required>
                            </div>
                            <div class="text-end mt-2">
                                <a href="forgot_password.php" class="text-primary text-decoration-none small">
                                    Lupa Password?
                                </a>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-lg mb-3">
                            Sign In <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                        <div class="text-center">
                            <span class="text-muted">Don't have an account?</span>
                            <a href="register.php" class="text-primary fw-bold text-decoration-none">Register</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
