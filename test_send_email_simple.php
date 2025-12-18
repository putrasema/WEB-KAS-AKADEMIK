<?php
/**
 * Simple test page untuk kirim email langsung
 * Buka di browser: http://localhost/academic_cash_system/test_send_email_simple.php
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

echo "<h2>Test Pengiriman Email</h2>";

// Get a student with email
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM students WHERE email IS NOT NULL AND email != '' LIMIT 1");
$student = $stmt->fetch();

if (!$student) {
    die("ERROR: Tidak ada mahasiswa dengan email di database");
}

echo "<p>Mengirim test email ke:</p>";
echo "<ul>";
echo "<li><strong>Nama:</strong> {$student['full_name']}</li>";
echo "<li><strong>Email:</strong> {$student['email']}</li>";
echo "</ul>";

$notificationService = new NotificationService();

echo "<p>Mengirim email...</p>";

$result = $notificationService->sendPaymentReminder(
    $student,
    'KAS-BULAN',
    'December 2025',
    'http://' . $_SERVER['HTTP_HOST'] . '/academic_cash_system/transactions_add.php'
);

echo "<hr>";
echo "<h3>Hasil:</h3>";

if ($result['success']) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
    echo "<strong style='color: #155724;'>✅ SUCCESS!</strong><br>";
    echo "Message: " . htmlspecialchars($result['message']);
    echo "</div>";
    echo "<p>Silakan cek inbox email: <strong>{$student['email']}</strong></p>";
    echo "<p><small>Jika tidak ada di inbox, cek folder Spam/Junk</small></p>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<strong style='color: #721c24;'>❌ FAILED!</strong><br>";
    echo "<pre>" . htmlspecialchars($result['message']) . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='notifications.php'>← Kembali ke Notifikasi & Pengingat</a></p>";
