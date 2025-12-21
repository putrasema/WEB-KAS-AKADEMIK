<?php


require_once __DIR__ . '/../src/Config/init.php';

echo "===========================================\n";
echo "  UBAH PASSWORD USER MANUAL  \n";
echo "===========================================\n\n";

try {
    $pdo = $db->getConnection();


    echo "Masukkan Username yang akan diubah passwordnya (default: admin): ";
    $handle = fopen("php://stdin", "r");
    $username = trim(fgets($handle));
    if (empty($username)) {
        $username = 'admin';
    }


    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "❌ User '$username' tidak ditemukan!\n";
        exit(1);
    }

    echo "✓ User ditemukan: {$user['full_name']} ({$user['role']})\n\n";


    echo "Masukkan Password Baru: ";
    $password = trim(fgets($handle));

    if (empty($password)) {
        echo "❌ Password tidak boleh kosong!\n";
        exit(1);
    }


    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_password, $user['id']]);

    echo "\n===========================================\n";
    echo "  PASSWORD BERHASIL DIUBAH!  \n";
    echo "===========================================\n\n";
    echo "Detail Update:\n";
    echo "Username : $username\n";
    echo "Password : $password\n";
    echo "Role     : {$user['role']}\n\n";
    echo "Silakan login dengan password baru.\n";

    fclose($handle);

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
?>