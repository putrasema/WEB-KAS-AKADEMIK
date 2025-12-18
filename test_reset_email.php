<?php
/**
 * Test page untuk debug email reset password
 * Buka di browser: http://localhost/academic_cash_system/test_reset_email.php
 */

require_once 'config/init.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    die("ERROR: Anda harus login sebagai admin terlebih dahulu.<br><a href='login.php'>Login di sini</a>");
}

$currentUser = $auth->getCurrentUser();
if ($currentUser['role'] !== 'admin') {
    die("ERROR: Hanya admin yang dapat mengakses halaman ini.");
}

require_once 'classes/NotificationService.php';

echo "<h2>Test Email Reset Password</h2>";

// Get database connection
$db = Database::getInstance()->getConnection();

// Check if users table has email column
echo "<h3>1. Cek Struktur Database</h3>";
$stmt = $db->query("DESCRIBE users");
$columns = $stmt->fetchAll();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
foreach ($columns as $col) {
    $highlight = in_array($col['Field'], ['email', 'reset_token', 'reset_token_expires']) ? ' style="background-color: #ffffcc;"' : '';
    echo "<tr{$highlight}>";
    echo "<td>{$col['Field']}</td>";
    echo "<td>{$col['Type']}</td>";
    echo "<td>{$col['Null']}</td>";
    echo "<td>{$col['Key']}</td>";
    echo "<td>{$col['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

// Check if admin has email
echo "<h3>2. Cek Email Admin</h3>";
$stmt = $db->query("SELECT id, username, full_name, email FROM users WHERE role = 'admin'");
$admins = $stmt->fetchAll();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th></tr>";
foreach ($admins as $admin) {
    $emailHighlight = empty($admin['email']) ? ' style="background-color: #ffcccc;"' : ' style="background-color: #ccffcc;"';
    echo "<tr>";
    echo "<td>{$admin['id']}</td>";
    echo "<td>{$admin['username']}</td>";
    echo "<td>{$admin['full_name']}</td>";
    echo "<td{$emailHighlight}>" . ($admin['email'] ?: 'TIDAK ADA EMAIL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Get first admin with email
$stmt = $db->query("SELECT * FROM users WHERE role = 'admin' AND email IS NOT NULL AND email != '' LIMIT 1");
$testUser = $stmt->fetch();

if (!$testUser) {
    echo "<div style='background: #ffcccc; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<strong>❌ ERROR:</strong> Tidak ada admin dengan email di database!<br><br>";
    echo "Silakan update email admin dengan query SQL:<br>";
    echo "<code>UPDATE users SET email = 'your-email@gmail.com' WHERE username = 'admin';</code>";
    echo "</div>";
    die();
}

echo "<h3>3. Test User yang Akan Digunakan</h3>";
echo "<ul>";
echo "<li><strong>Username:</strong> {$testUser['username']}</li>";
echo "<li><strong>Full Name:</strong> {$testUser['full_name']}</li>";
echo "<li><strong>Email:</strong> {$testUser['email']}</li>";
echo "</ul>";

// Generate test token and link
echo "<h3>4. Generate Token & Link</h3>";
$resetToken = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

echo "<ul>";
echo "<li><strong>Token:</strong> " . substr($resetToken, 0, 20) . "... (64 karakter)</li>";
echo "<li><strong>Expires At:</strong> {$expiresAt}</li>";
echo "</ul>";

// Create reset link
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$resetLink = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']) . '/reset_password.php?token=' . $resetToken;

echo "<p><strong>Reset Link:</strong><br>";
echo "<a href='{$resetLink}' target='_blank'>{$resetLink}</a></p>";

// Save token to database
echo "<h3>5. Simpan Token ke Database</h3>";
$stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
$result = $stmt->execute([$resetToken, $expiresAt, $testUser['id']]);

if ($result) {
    echo "<div style='background: #ccffcc; padding: 10px; border-radius: 5px;'>";
    echo "✅ Token berhasil disimpan ke database";
    echo "</div>";
} else {
    echo "<div style='background: #ffcccc; padding: 10px; border-radius: 5px;'>";
    echo "❌ Gagal menyimpan token ke database";
    echo "</div>";
}

// Test email sending
echo "<h3>6. Test Kirim Email</h3>";
echo "<p>Mengirim email reset password ke: <strong>{$testUser['email']}</strong></p>";

$notificationService = new NotificationService();
$emailResult = $notificationService->sendPasswordResetEmail($testUser, $resetToken, $resetLink);

echo "<hr>";
echo "<h3>Hasil Pengiriman Email:</h3>";

if ($emailResult['success']) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
    echo "<strong style='color: #155724;'>✅ SUCCESS!</strong><br>";
    echo "Message: " . htmlspecialchars($emailResult['message']);
    echo "</div>";
    echo "<p>Silakan cek inbox email: <strong>{$testUser['email']}</strong></p>";
    echo "<p><small>Jika tidak ada di inbox, cek folder Spam/Junk</small></p>";

    echo "<hr>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Cek email inbox Anda</li>";
    echo "<li>Klik link reset password di email</li>";
    echo "<li>Atau copy link ini: <a href='{$resetLink}' target='_blank'>Reset Password</a></li>";
    echo "</ol>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<strong style='color: #721c24;'>❌ FAILED!</strong><br>";
    echo "<pre>" . htmlspecialchars($emailResult['message']) . "</pre>";
    echo "</div>";

    echo "<hr>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Pastikan email config sudah benar di <code>config/email_config.php</code></li>";
    echo "<li>Pastikan sudah menggunakan App Password dari Google (bukan password biasa)</li>";
    echo "<li>Pastikan 2-Step Verification sudah aktif di akun Google</li>";
    echo "<li>Lihat file <code>SETUP_EMAIL.md</code> untuk panduan lengkap</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='forgot_password.php'>← Test Forgot Password Form</a> | ";
echo "<a href='notifications.php'>Lihat Log Notifikasi</a></p>";
?>