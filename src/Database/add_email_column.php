<?php
/**
 * Database Migration: Add email column to students table
 * This script will add the email column if it doesn't exist
 */

require_once __DIR__ . '/../config/init.php';

try {
    $db = Database::getInstance()->getConnection();

    // Check if email column exists
    $stmt = $db->query("SHOW COLUMNS FROM students LIKE 'email'");
    $columnExists = $stmt->fetch();

    if (!$columnExists) {
        echo "Adding email column to students table...\n";

        // Add email column after full_name
        $db->exec("ALTER TABLE students ADD COLUMN email VARCHAR(100) DEFAULT NULL AFTER full_name");

        echo "✅ Email column added successfully!\n";
    } else {
        echo "✅ Email column already exists in students table.\n";
    }

    echo "\n✅ Migration completed successfully!\n";

} catch (PDOException $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
