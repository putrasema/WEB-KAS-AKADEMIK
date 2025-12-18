<?php
/**
 * Simple test untuk cek database dan kirim email reset password
 * TIDAK PERLU LOGIN - langsung bisa diakses
 */

// Load config
require_once 'config/config.php';
require_once 'config/Database.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test Reset Password</title></head><body>";
echo "<h2>🔍 Test Email Reset Password - Simple Version</h2>";

// Get database connection
$db = Database::getInstance()->getConnection();

// 1. Check database structure
echo "<h3>1. Cek Struktur Database</h3>";
try {
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll();

    $hasEmail = false;
    $hasResetToken = false;
    $hasResetExpires = false;

    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Status</th></tr>";

    foreach ($columns as $col) {
        $fieldName = $col['Field'];
        if ($fieldName === 'email')
            $hasEmail = true;
        if ($fieldName === 'reset_token')
            $hasResetToken = true;
        if ($fieldName === 'reset_token_expires')
            $hasResetExpires = true;

        $highlight = in_array($fieldName, ['email', 'reset_token', 'reset_token_expires']) ?
            ' style="background-color: #ccffcc;"' : '';

        echo "<tr{$highlight}>";
        echo "<td><strong>{$fieldName}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>" . (in_array($fieldName, ['email', 'reset_token', 'reset_token_expires']) ? '✅ OK' : '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Check if all required columns exist
    echo "<div style='margin: 15px 0; padding: 10px; border-radius: 5px; ";
    if ($hasEmail && $hasResetToken && $hasResetExpires) {
        echo "background: #d4edda; border: 1px solid #c3e6cb;'>";
        echo "<strong style='color: #155724;'>✅ Database OK!</strong> Semua kolom yang diperlukan sudah ada.";
    } else {
        echo "background: #f8d7da; border: 1px solid #f5c6cb;'>";
        echo "<strong style='color: #721c24;'>❌ Database Belum Lengkap!</strong><br><br>";
        echo "Kolom yang kurang:<br>";
        if (!$hasEmail)
            echo "- email<br>";
        if (!$hasResetToken)
            echo "- reset_token<br>";
        if (!$hasResetExpires)
            echo "- reset_token_expires<br>";
        echo "<br>Silakan jalankan migration SQL di phpMyAdmin:<br>";
        echo "<textarea style='width: 100%; height: 150px; margin-top: 10px;'>";
        echo "ALTER TABLE `users` \n";
        echo "ADD COLUMN IF NOT EXISTS `email` VARCHAR(100) DEFAULT NULL AFTER `full_name`,\n";
        echo "ADD UNIQUE KEY IF NOT EXISTS `email` (`email`);\n\n";
        echo "ALTER TABLE `users` \n";
        echo "ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(100) DEFAULT NULL AFTER `email`,\n";
        echo "ADD COLUMN IF NOT EXISTS `reset_token_expires` DATETIME DEFAULT NULL AFTER `reset_token`;\n\n";
        echo "COMMIT;";
        echo "</textarea>";
        echo "</div>";
        die();
    }
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
    die();
}

// 2. Check admin email
echo "<h3>2. Cek Email Admin</h3>";
$stmt = $db->query("SELECT id, username, full_name, email FROM users WHERE role = 'admin'");
$admins = $stmt->fetchAll();

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Status</th></tr>";

$hasAdminWithEmail = false;
foreach ($admins as $admin) {
    $hasEmail = !empty($admin['email']);
    if ($hasEmail)
        $hasAdminWithEmail = true;

    $emailBg = $hasEmail ? '#ccffcc' : '#ffcccc';
    echo "<tr>";
    echo "<td>{$admin['id']}</td>";
    echo "<td><strong>{$admin['username']}</strong></td>";
    echo "<td>{$admin['full_name']}</td>";
    echo "<td style='background: {$emailBg};'>" . ($admin['email'] ?: 'TIDAK ADA EMAIL') . "</td>";
    echo "<td>" . ($hasEmail ? '✅ OK' : '❌ Perlu Update') . "</td>";
    echo "</tr>";
}
echo "</table>";

if (!$hasAdminWithEmail) {
    echo "<div style='background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 5px; border: 1px solid #ffc107;'>";
    echo "<strong style='color: #856404;'>⚠️ Perlu Update Email!</strong><br><br>";
    echo "Silakan update email admin di phpMyAdmin:<br>";
    echo "<textarea style='width: 100%; height: 60px; margin-top: 10px;'>";
    echo "UPDATE `users` SET `email` = 'keisyaaurora325@gmail.com' WHERE `username` = 'admin';";
    echo "</textarea>";
    echo "</div>";
    die();
}

// 3. Test send email
echo "<h3>3. Test Kirim Email Reset Password</h3>";

// Get first admin with email
$stmt = $db->query("SELECT * FROM users WHERE role = 'admin' AND email IS NOT NULL AND email != '' LIMIT 1");
$testUser = $stmt->fetch();

echo "<p>Test user: <strong>{$testUser['username']}</strong> ({$testUser['email']})</p>";

// Generate token
$resetToken = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

echo "<p>Token: <code>" . substr($resetToken, 0, 20) . "...</code> (expires: {$expiresAt})</p>";

// Save token to database
$stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
$stmt->execute([$resetToken, $expiresAt, $testUser['id']]);

// Create reset link
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$resetLink = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']) . '/reset_password.php?token=' . $resetToken;

echo "<p>Reset link: <a href='{$resetLink}' target='_blank'>{$resetLink}</a></p>";

// Send email
require_once 'classes/NotificationService.php';
$notificationService = new NotificationService();

echo "<hr><h3>📧 Mengirim Email...</h3>";
$result = $notificationService->sendPasswordResetEmail($testUser, $resetToken, $resetLink);

if ($result['success']) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px;'>";
    echo "<h2 style='color: #155724; margin-top: 0;'>✅ EMAIL BERHASIL DIKIRIM!</h2>";
    echo "<p><strong>Ke:</strong> {$testUser['email']}</p>";
    echo "<p>{$result['message']}</p>";
    echo "<hr>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Cek inbox email <strong>{$testUser['email']}</strong></li>";
    echo "<li>Cari email dengan subject: <strong>\"Reset Password - Sistem Kas Akademik\"</strong></li>";
    echo "<li>Jika tidak ada di Inbox, cek folder <strong>Spam/Junk</strong></li>";
    echo "<li>Klik link di email, atau <a href='{$resetLink}' target='_blank'>klik di sini</a></li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 5px;'>";
    echo "<h2 style='color: #721c24; margin-top: 0;'>❌ EMAIL GAGAL DIKIRIM!</h2>";
    echo "<pre style='background: white; padding: 10px; border-radius: 3px; overflow-x: auto;'>";
    echo htmlspecialchars($result['message']);
    echo "</pre>";
    echo "<hr>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Cek file <code>config/email_config.php</code></li>";
    echo "<li>Pastikan SMTP_USERNAME dan SMTP_PASSWORD sudah benar</li>";
    echo "<li>Pastikan menggunakan App Password dari Google (bukan password biasa)</li>";
    echo "<li>Lihat file <code>SETUP_EMAIL.md</code> untuk panduan</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='login.php'>← Kembali ke Login</a> | ";
echo "<a href='forgot_password.php'>Test Forgot Password Form</a></p>";
echo "</body></html>";
?>