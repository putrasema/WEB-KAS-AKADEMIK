<?php
require_once 'config/init.php';

echo "<h2>🕵️ Diagnostic Tools</h2>";

// 1. Check Users
echo "<h3>1. Daftar User di Database</h3>";
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, username, full_name, email, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($users) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #eee;'><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Len(User)</th></tr>";
    foreach ($users as $u) {
        echo "<tr>";
        echo "<td>{$u['id']}</td>";
        echo "<td>'<strong>{$u['username']}</strong>'</td>"; // Quote to see whitespace
        echo "<td>" . ($u['email'] ?: '<span style="color:red">NULL</span>') . "</td>";
        echo "<td>{$u['role']}</td>";
        echo "<td>" . strlen($u['username']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>TIDAK ADA USER DI DATABASE!</p>";
}

// 2. Test Search 'admin'
echo "<h3>2. Test Query Username 'admin'</h3>";
$testName = 'admin';
$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$testName]);
$user = $stmt->fetch();

if ($user) {
    echo "<div style='color:green'>✅ User 'admin' DITEMUKAN. ID: {$user['id']}</div>";
} else {
    echo "<div style='color:red'>❌ User 'admin' TIDAK DITEMUKAN dengan query exact match.</div>";

    // Try LIKE
    $stmt = $db->prepare("SELECT * FROM users WHERE username LIKE ?");
    $stmt->execute(["%admin%"]);
    $approximates = $stmt->fetchAll();
    if ($approximates) {
        echo "<p>Tapi ditemukan user yang mirip:</p><ul>";
        foreach ($approximates as $app) {
            echo "<li>'{$app['username']}' (ID: {$app['id']})</li>";
        }
        echo "</ul>";
    }
}

// 3. Email Config Check
echo "<h3>3. Cek Config Email</h3>";
require_once 'config/email_config.php';
echo "<ul>";
echo "<li>SMTP_USERNAME: " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : 'Not Defined') . "</li>";
echo "<li>SMTP_PASSWORD: " . (defined('SMTP_PASSWORD') ? (strlen(SMTP_PASSWORD) > 5 ? '******' : 'Too Short') : 'Not Defined') . "</li>";
echo "</ul>";

echo "<hr><a href='forgot_password.php'>Kembali ke Forgot Password</a>";
?>