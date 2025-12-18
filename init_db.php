<?php
require_once 'config/database.php';

try {
    $sql = file_get_contents('database/schema.sql');
    $pdo->exec($sql);
    echo "Database initialized successfully successfully.<br>";
    echo "Default admin created (user: admin, pass: password).<br>";
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
