<?php
require_once __DIR__ . '/../src/Config/init.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $fullName = $_POST['full_name'];

    try {
        if ($auth->register($username, $password, $fullName)) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Registration failed. Username might be taken.";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Academic Cash System</title>
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
                        <h3 class="fw-bold">Create Account</h3>
                        <p class="text-muted">BUAT AKUN AKADEMIK KAS SISTEM</p>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success text-center border-0 bg-success-subtle text-success">
                            <i class="bi bi-check-circle me-2"></i> <?= $success ?>
                            <div class="mt-2">
                                <a href="login.php" class="btn btn-sm btn-success px-4">Login Now</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger text-center border-0 bg-danger-subtle text-danger">
                            <i class="bi bi-exclamation-circle me-2"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-muted small">NAMA LENGKAP</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i
                                        class="bi bi-person-badge text-muted"></i></span>
                                <input type="text" name="full_name" class="form-control border-start-0 ps-0"
                                    placeholder="masukan nama" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-muted small">USERNAME</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i
                                        class="bi bi-person text-muted"></i></span>
                                <input type="text" name="username" class="form-control border-start-0 ps-0"
                                    placeholder="username ( if24a.nama )" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-medium text-muted small">PASSWORD</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i
                                        class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0"
                                    placeholder="buat password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-lg mb-3">
                            Register <i class="bi bi-person-plus ms-2"></i>
                        </button>
                        <div class="text-center">
                            <span class="text-muted">Already have an account?</span>
                            <a href="login.php" class="text-primary fw-bold text-decoration-none">Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
