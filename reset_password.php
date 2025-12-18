<?php
require_once 'config/init.php';

// If already logged in, we should logout first to allow password reset
// This prevents the "redirect to dashboard" issue when testing
if ($auth->isLoggedIn()) {
    $auth->logout(); 
}

$message = '';
$messageType = '';
$validToken = false;
$token = $_GET['token'] ?? '';

// Check if token is provided and valid
if (!empty($token)) {
    $db = Database::getInstance()->getConnection();

    // 1. Find user with this token (ignore expiration for now)
    $stmt = $db->prepare("SELECT * FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Check expiration in PHP to ensure timezone consistency
        $expiresAt = strtotime($user['reset_token_expires']);
        $now = time();
        
        if ($now <= $expiresAt) {
            $validToken = true;
        } else {
            // Token expired
            $diff = $now - $expiresAt;
            $message = "Link reset password sudah kadaluarsa " . ceil($diff/60) . " menit yang lalu.<br>" .
                       "<small class='text-muted'>Server Time: " . date('H:i', $now) . " | Expires: " . date('H:i', $expiresAt) . "</small>";
            $messageType = 'danger';
        }
    } else {
        $message = 'Link reset password tidak valid atau token salah.';
        $messageType = 'danger';
    }
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($password) || empty($confirmPassword)) {
        $message = 'Password tidak boleh kosong';
        $messageType = 'danger';
    } elseif ($password !== $confirmPassword) {
        $message = 'Password dan konfirmasi password tidak cocok';
        $messageType = 'danger';
    } elseif (strlen($password) < 6) {
        $message = 'Password minimal 6 karakter';
        $messageType = 'danger';
    } else {
        // Hash new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update password and clear reset token
        $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $stmt->execute([$hashedPassword, $user['id']]);

        // Redirect to login with success message
        $_SESSION['reset_success'] = true;
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Academic Cash System</title>
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
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <h3 class="fw-bold">Reset Password</h3>
                        <p class="text-muted">Masukkan password baru Anda</p>
                    </div>

                    <?php if ($message): ?>
                        <div
                            class="alert alert-<?= $messageType ?> py-2 text-center border-0 bg-<?= $messageType ?>-subtle text-<?= $messageType ?>">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <?= $message ?>
                        </div>

                        <?php if ($messageType === 'danger'): ?>
                            <div class="text-center mt-3">
                                <a href="forgot_password.php" class="btn btn-primary">
                                    Request Reset Ulang
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($validToken && empty($message)): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-medium text-muted small">PASSWORD BARU</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i
                                            class="bi bi-lock text-muted"></i></span>
                                    <input type="password" name="password" id="password"
                                        class="form-control border-start-0 ps-0" placeholder="masukan password baru"
                                        required minlength="6">
                                </div>
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-medium text-muted small">KONFIRMASI PASSWORD</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i
                                            class="bi bi-lock-fill text-muted"></i></span>
                                    <input type="password" name="confirm_password" id="confirm_password"
                                        class="form-control border-start-0 ps-0" placeholder="ulangi password baru" required
                                        minlength="6">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-lg mb-3">
                                Reset Password <i class="bi bi-check-circle ms-2"></i>
                            </button>

                            <div class="text-center">
                                <a href="login.php" class="text-muted text-decoration-none">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
                                </a>
                            </div>
                        </form>
                    <?php elseif (empty($token)): ?>
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Token tidak ditemukan. Silakan request reset password terlebih dahulu.
                        </div>
                        <div class="text-center mt-3">
                            <a href="forgot_password.php" class="btn btn-primary">
                                Request Reset Password
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Client-side password match validation
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function (e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;

                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('Password dan konfirmasi password tidak cocok!');
                        return false;
                    }
                });
            }
        });
    </script>
</body>

</html>