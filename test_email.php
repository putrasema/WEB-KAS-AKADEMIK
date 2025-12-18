<?php
/**
 * Test SMTP Connection and Email Sending
 */

require_once 'config/init.php';
require_once 'classes/NotificationService.php';

echo "=== TEST KONEKSI SMTP GMAIL ===\n\n";

// Display current configuration (hide password)
echo "Konfigurasi saat ini:\n";
echo "- SMTP Host: " . SMTP_HOST . "\n";
echo "- SMTP Port: " . SMTP_PORT . "\n";
echo "- SMTP Secure: " . SMTP_SECURE . "\n";
echo "- SMTP Username: " . SMTP_USERNAME . "\n";
echo "- SMTP Password: " . str_repeat("*", strlen(SMTP_PASSWORD)) . " (" . strlen(SMTP_PASSWORD) . " karakter)\n";
echo "- From Email: " . SMTP_FROM_EMAIL . "\n\n";

// Check if configuration is set
echo "Checking konfigurasi...\n";
if (SMTP_USERNAME === 'your-email@gmail.com' || SMTP_PASSWORD === 'your-app-password-here') {
    echo "❌ ERROR: Konfigurasi masih menggunakan placeholder!\n";
    echo "Silakan update config/email_config.php dengan kredensial Gmail Anda.\n";
    exit(1);
}
echo "✅ Konfigurasi sudah diupdate\n\n";

// Test email sending
echo "=== TEST PENGIRIMAN EMAIL ===\n\n";

$notificationService = new NotificationService();

// Get a student with email
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM students WHERE email IS NOT NULL AND email != '' LIMIT 1");
$student = $stmt->fetch();

if (!$student) {
    echo "❌ Tidak ada mahasiswa dengan email di database\n";
    exit(1);
}

echo "Mengirim test email ke:\n";
echo "- Nama: {$student['full_name']}\n";
echo "- Email: {$student['email']}\n\n";

echo "Mengirim email...\n";

$result = $notificationService->sendPaymentReminder(
    $student,
    'KAS-BULAN',
    'December 2025',
    'http://localhost/academic_cash_system/transactions_add.php'
);

echo "\n=== HASIL ===\n";
if ($result['success']) {
    echo "✅ SUCCESS!\n";
    echo "Message: {$result['message']}\n\n";
    echo "Silakan cek inbox email: {$student['email']}\n";
    echo "Jika tidak ada di inbox, cek folder Spam/Junk\n";
} else {
    echo "❌ FAILED!\n";
    echo "Error: {$result['message']}\n\n";

    // Check common issues
    echo "\n=== TROUBLESHOOTING ===\n";
    if (strpos($result['message'], 'authenticate') !== false) {
        echo "Kemungkinan penyebab:\n";
        echo "1. App Password salah - pastikan copy dengan benar\n";
        echo "2. 2-Step Verification belum aktif\n";
        echo "3. Email salah\n";
    } elseif (strpos($result['message'], 'connect') !== false) {
        echo "Kemungkinan penyebab:\n";
        echo "1. Koneksi internet bermasalah\n";
        echo "2. Port 587 diblok oleh firewall/antivirus\n";
        echo "3. Coba ganti ke port 465 dengan SSL\n";
    }
}

// Check notification log
echo "\n=== LOG NOTIFIKASI (5 Terakhir) ===\n";
$stmt = $db->query("SELECT * FROM notifications WHERE type = 'email' ORDER BY created_at DESC LIMIT 5");
$logs = $stmt->fetchAll();

if (empty($logs)) {
    echo "Belum ada log notifikasi\n";
} else {
    foreach ($logs as $log) {
        echo "\n- [{$log['status']}] {$log['title']}\n";
        echo "  Message: {$log['message']}\n";
        echo "  Time: {$log['created_at']}\n";
    }
}
