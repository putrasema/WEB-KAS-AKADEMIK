<?php
require_once __DIR__ . '/../src/Config/init.php';



// If already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);

    if (empty($username)) {
        $message = 'Username tidak boleh kosong';
        $messageType = 'danger';
    } else {
        // Get database connection
        $db = Database::getInstance()->getConnection();

        // Find user by username
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            // Don't reveal if user exists or not for security
            $message = 'Jika username terdaftar, link reset password telah dikirim ke email Anda.';
            $messageType = 'success';
        } elseif (empty($user['email'])) {
            $message = 'Akun ini belum memiliki email terdaftar. Silakan hubungi administrator.';
            $messageType = 'warning';
        } else {
            // Generate reset token
            $resetToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Save token to database
            $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
            $stmt->execute([$resetToken, $expiresAt, $user['id']]);

            // Create reset link - FIX: Handle Windows backslashes
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];

            // Clean up path - replace backslash with forward slash and trim trailing slash
            $path = dirname($_SERVER['PHP_SELF']);
            $path = str_replace('\\', '/', $path);
            if ($path === '/')
                $path = ''; // Remove root slash to avoid double slash

            $resetLink = $protocol . '://' . $host . $path . '/reset_password.php?token=' . $resetToken;

            // Send email
            require_once 'classes/NotificationService.php';
            $notificationService = new NotificationService();
            $result = $notificationService->sendPasswordResetEmail($user, $resetToken, $resetLink);

            if ($result['success']) {
                $message = 'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam.';
                $messageType = 'success';
            } else {
                $message = 'Gagal mengirim email. ' . $result['message'];
                $messageType = 'danger';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Academic Cash System</title>
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
                            <i class="bi bi-key"></i>
                        </div>
                        <h3 class="fw-bold">Lupa Password?</h3>
                        <p class="text-muted">Masukkan username Anda untuk reset password</p>
                    </div>

                    <?php if ($message): ?>
                        <div
                            class="alert alert-<?= $messageType ?> py-2 text-center border-0 bg-<?= $messageType ?>-subtle text-<?= $messageType ?>">
                            <i
                                class="bi bi-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                            <?= $message ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-medium text-muted small">USERNAME</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i
                                        class="bi bi-person text-muted"></i></span>
                                <input type="text" name="username" class="form-control border-start-0 ps-0"
                                    placeholder="masukan username" required autofocus>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-lg mb-3">
                            Kirim Link Reset <i class="bi bi-send ms-2"></i>
                        </button>

                        <div class="text-center">
                            <a href="login.php" class="text-muted text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
