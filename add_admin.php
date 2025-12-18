<?php
/**
 * Script untuk Menambahkan User Admin ke Database
 * Jalankan file ini untuk membuat user admin baru
 */

echo "===========================================\n";
echo "  TAMBAH USER ADMIN KE DATABASE  \n";
echo "===========================================\n\n";

// Konfigurasi Database
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'academic_cash_db';

try {
    // Koneksi ke database
    echo "[1/3] Menghubungkan ke database...\n";
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Berhasil terhubung ke database '$db_name'\n\n";

    // Cek apakah user admin sudah ada
    echo "[2/3] Memeriksa user admin...\n";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll();

    if (count($admins) > 0) {
        echo "⚠ User admin sudah ada:\n";
        foreach ($admins as $admin) {
            echo "   - ID: {$admin['id']}, Username: {$admin['username']}, Nama: {$admin['full_name']}\n";
        }
        echo "\n";

        echo "Apakah Anda ingin menambah admin baru? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);

        if (trim($line) != 'y' && trim($line) != 'Y') {
            echo "\nProses dibatalkan.\n";
            exit(0);
        }
    }

    // Input data admin baru
    echo "\n[3/3] Membuat user admin baru...\n";
    echo "----------------------------------------\n";

    // Username
    echo "Masukkan username admin (default: admin): ";
    $handle = fopen("php://stdin", "r");
    $username = trim(fgets($handle));
    if (empty($username)) {
        $username = 'admin';
    }
    fclose($handle);

    // Cek apakah username sudah ada
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()['count'] > 0) {
        echo "❌ Username '$username' sudah digunakan!\n";
        echo "Silakan jalankan script lagi dengan username berbeda.\n";
        exit(1);
    }

    // Password
    echo "Masukkan password admin (default: password): ";
    $handle = fopen("php://stdin", "r");
    $password = trim(fgets($handle));
    if (empty($password)) {
        $password = 'password';
    }
    fclose($handle);

    // Full Name
    echo "Masukkan nama lengkap (default: Administrator): ";
    $handle = fopen("php://stdin", "r");
    $full_name = trim(fgets($handle));
    if (empty($full_name)) {
        $full_name = 'Administrator';
    }
    fclose($handle);

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert ke database
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
    $stmt->execute([$username, $hashed_password, $full_name]);

    $new_id = $pdo->lastInsertId();

    echo "\n===========================================\n";
    echo "  USER ADMIN BERHASIL DIBUAT!  \n";
    echo "===========================================\n\n";

    echo "Detail User Admin:\n";
    echo "-------------------\n";
    echo "ID       : $new_id\n";
    echo "Username : $username\n";
    echo "Password : $password\n";
    echo "Nama     : $full_name\n";
    echo "Role     : admin\n\n";

    echo "Informasi Login:\n";
    echo "----------------\n";
    echo "URL      : http://localhost:8000/login.php\n";
    echo "Username : $username\n";
    echo "Password : $password\n\n";

    // Tampilkan semua admin
    echo "Daftar Semua Admin:\n";
    echo "-------------------\n";
    $stmt = $pdo->query("SELECT id, username, full_name, created_at FROM users WHERE role = 'admin' ORDER BY id");
    $all_admins = $stmt->fetchAll();

    foreach ($all_admins as $admin) {
        echo "- ID: {$admin['id']}, Username: {$admin['username']}, Nama: {$admin['full_name']}, Dibuat: {$admin['created_at']}\n";
    }
    echo "\n";

    echo "✅ Proses selesai!\n";
    echo "Anda sekarang bisa login sebagai admin.\n\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR DATABASE: " . $e->getMessage() . "\n\n";
    echo "Troubleshooting:\n";
    echo "----------------\n";
    echo "1. Pastikan MySQL/XAMPP sudah berjalan\n";
    echo "2. Pastikan database 'academic_cash_db' sudah dibuat\n";
    echo "3. Periksa kredensial database (host, user, password)\n";
    echo "4. Jalankan 'php setup_database.php' terlebih dahulu\n\n";
    exit(1);
}
?>