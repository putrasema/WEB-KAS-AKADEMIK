<?php
require_once 'config/init.php';

echo "<h2>🔧 Auto-Fix & Check</h2>";

$db = Database::getInstance()->getConnection();

// 1. Cek User 'admin'
$stmt = $db->prepare("SELECT * FROM users WHERE username = 'admin'");
$stmt->execute();
$admin = $stmt->fetch();

if ($admin) {
    echo "<div style='background:#e6fffa; padding:10px; border-radius:5px; margin-bottom:10px;'>";
    echo "✅ User 'admin' ditemukan (ID: {$admin['id']})<br>";

    // Cek Email
    if (empty($admin['email'])) {
        echo "⚠️ Email KOSONG. Memperbaiki...<br>";
        $update = $db->prepare("UPDATE users SET email = 'keisyaaurora325@gmail.com' WHERE id = ?");
        $update->execute([$admin['id']]);
        echo "✅ Email berhasil di-update ke: keisyaaurora325@gmail.com<br>";
    } else {
        echo "✅ Email saat ini: <strong>{$admin['email']}</strong><br>";
        // Update to correct email just in case
        if ($admin['email'] !== 'keisyaaurora325@gmail.com') {
            $update = $db->prepare("UPDATE users SET email = 'keisyaaurora325@gmail.com' WHERE id = ?");
            $update->execute([$admin['id']]);
            echo "🔄 Email di-update ke email default: keisyaaurora325@gmail.com<br>";
        }
    }
    echo "</div>";
} else {
    echo "<div style='background:#fff5f5; padding:10px; border-radius:5px; margin-bottom:10px;'>";
    echo "❌ User 'admin' TIDAK DITEMUKAN!<br>";
    // Create admin
    $pass = password_hash('password', PASSWORD_DEFAULT);
    $create = $db->prepare("INSERT INTO users (username, password, full_name, role, email) VALUES ('admin', ?, 'Administrator', 'admin', 'keisyaaurora325@gmail.com')");
    $create->execute([$pass]);
    echo "✅ User 'admin' berhasil dibuat ulang dengan password default 'password'.<br>";
    echo "</div>";
}

// 2. Cek Kolom Database
echo "<h3>2. Cek Struktur Database</h3>";
try {
    $cols = $db->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN);
    $missing = [];
    if (!in_array('email', $cols))
        $missing[] = 'email';
    if (!in_array('reset_token', $cols))
        $missing[] = 'reset_token';

    if ($missing) {
        echo "❌ Kolom Hilang: " . implode(", ", $missing) . "<br>";
        echo "Silakan jalankan migration lagi.<br>";
    } else {
        echo "✅ Semua kolom database OK.<br>";
    }
} catch (Exception $e) {
    echo "Error checking DB: " . $e->getMessage();
}

echo "<hr>";
echo "<a href='forgot_password.php' style='background:blue; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Coba Forgot Password Lagi →</a>";
?>