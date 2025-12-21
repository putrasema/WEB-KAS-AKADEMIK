<?php
require_once __DIR__ . '/../src/Config/init.php';

echo "<!DOCTYPE html><html><head><title>Database Migration</title><style>body{font-family:sans-serif;padding:20px;max-width:800px;margin:0 auto;line-height:1.6}.success{color:green;background:#d4edda;padding:10px;border-radius:5px;margin:5px 0}.error{color:721c24;background:#f8d7da;padding:10px;border-radius:5px;margin:5px 0}</style></head><body>";
echo "<h2>🛠️ Database Migration Tool</h2>";

$db = Database::getInstance()->getConnection();

function runQuery($db, $sql, $message)
{
    try {
        $db->exec($sql);
        echo "<div class='success'>✅ $message</div>";
    } catch (PDOException $e) {

        if (strpos($e->getMessage(), "Duplicate column name") !== false) {
            echo "<div class='success'>ℹ️ $message (Already exists)</div>";
        } else {
            echo "<div class='error'>❌ Gagal: $message - " . $e->getMessage() . "</div>";
        }
    }
}


$sql1 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS email VARCHAR(100) DEFAULT NULL AFTER full_name";
$sql2 = "ALTER TABLE users ADD UNIQUE KEY IF NOT EXISTS email (email)";
runQuery($db, $sql1, "Menambahkan kolom 'email'");
runQuery($db, $sql2, "Menambahkan index unik untuk email");


$sql3 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(100) DEFAULT NULL AFTER email";
$sql4 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token_expires DATETIME DEFAULT NULL AFTER reset_token";
runQuery($db, $sql3, "Menambahkan kolom 'reset_token'");
runQuery($db, $sql4, "Menambahkan kolom 'reset_token_expires'");


$sql5 = "UPDATE users SET email = 'keisyaaurora325@gmail.com' WHERE username = 'admin'";
runQuery($db, $sql5, "Set email admin ke: keisyaaurora325@gmail.com");

echo "<hr>";
echo "<h3>🎉 Migration Selesai!</h3>";
echo "<p>Sekarang database Anda sudah memiliki kolom yang dibutuhkan.</p>";
echo "<p><a href='login.php' style='background:blue;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Kembali ke Login & Coba Forgot Password</a></p>";
echo "</body></html>";
?>