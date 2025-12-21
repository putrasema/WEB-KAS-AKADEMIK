<?php


echo "===========================================\n";
echo "  SETUP DATABASE ACADEMIC CASH SYSTEM  \n";
echo "===========================================\n\n";


$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'academic_cash_db';

try {

    echo "[1/4] Menghubungkan ke MySQL...\n";
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Berhasil terhubung ke MySQL\n\n";


    echo "[2/4] Membuat database '$db_name'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '$db_name' berhasil dibuat/sudah ada\n\n";


    $pdo->exec("USE `$db_name`");


    echo "[3/4] Membuat tabel dan struktur database...\n";
    $schema_file = __DIR__ . '/database/schema.sql';

    if (!file_exists($schema_file)) {
        throw new Exception("File schema.sql tidak ditemukan di: $schema_file");
    }

    $sql = file_get_contents($schema_file);


    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function ($statement) {
            return !empty($statement) &&
                !preg_match('/^--/', $statement) &&
                !preg_match('/^\/\*/', $statement);
        }
    );

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {

                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    echo "✓ Struktur database berhasil dibuat\n\n";


    echo "[4/4] Membuat user admin...\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result['count'] > 0) {
        echo "⚠ User admin sudah ada\n";
        echo "   Username: admin\n";
        echo "   Password: password (gunakan password yang sudah ada)\n\n";


        echo "Apakah Anda ingin reset password admin? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);

        if (trim($line) == 'y' || trim($line) == 'Y') {
            $new_password = password_hash('password', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
            $stmt->execute([$new_password]);
            echo "✓ Password admin berhasil direset ke 'password'\n\n";
        }
    } else {

        $admin_password = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', $admin_password, 'Administrator', 'admin']);
        echo "✓ User admin berhasil dibuat\n\n";
    }


    echo "===========================================\n";
    echo "  SETUP SELESAI!  \n";
    echo "===========================================\n\n";
    echo "Informasi Login Admin:\n";
    echo "----------------------\n";
    echo "URL      : http://localhost:8000/login.php\n";
    echo "Username : admin\n";
    echo "Password : password\n";
    echo "Role     : Administrator\n\n";

    echo "Cara Menjalankan Sistem:\n";
    echo "------------------------\n";
    echo "1. Buka terminal/CMD\n";
    echo "2. Jalankan: php -S localhost:8000\n";
    echo "3. Buka browser: http://localhost:8000\n";
    echo "4. Login dengan kredensial di atas\n\n";


    echo "Statistik Database:\n";
    echo "-------------------\n";
    $tables = ['users', 'students', 'transactions', 'categories', 'currencies'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "- $table: $count record(s)\n";
        } catch (PDOException $e) {
            echo "- $table: Tabel tidak ditemukan\n";
        }
    }
    echo "\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR DATABASE: " . $e->getMessage() . "\n\n";
    echo "Troubleshooting:\n";
    echo "----------------\n";
    echo "1. Pastikan MySQL/XAMPP sudah berjalan\n";
    echo "2. Periksa kredensial database (host, user, password)\n";
    echo "3. Pastikan user MySQL memiliki hak akses CREATE DATABASE\n\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "✅ Setup database berhasil!\n";
echo "Anda sekarang bisa menjalankan sistem.\n\n";
?>