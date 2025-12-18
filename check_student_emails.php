<?php
require_once 'config/init.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, student_id_number, full_name, email FROM students WHERE status = 'active' ORDER BY student_id_number");

echo "=== DAFTAR MAHASISWA DAN EMAIL ===\n\n";
echo str_pad("NIM", 15) . str_pad("NAMA", 30) . "EMAIL\n";
echo str_repeat("-", 70) . "\n";

$totalStudents = 0;
$studentsWithEmail = 0;
$studentsWithoutEmail = [];

while ($row = $stmt->fetch()) {
    $totalStudents++;
    $email = $row['email'] ?: 'BELUM ADA';

    if ($row['email']) {
        $studentsWithEmail++;
    } else {
        $studentsWithoutEmail[] = [
            'id' => $row['id'],
            'nim' => $row['student_id_number'],
            'name' => $row['full_name']
        ];
    }

    echo str_pad($row['student_id_number'], 15) .
        str_pad($row['full_name'], 30) .
        $email . "\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "Total Mahasiswa: $totalStudents\n";
echo "Sudah punya email: $studentsWithEmail\n";
echo "Belum punya email: " . count($studentsWithoutEmail) . "\n";

if (!empty($studentsWithoutEmail)) {
    echo "\n⚠️ Mahasiswa yang belum punya email:\n";
    foreach ($studentsWithoutEmail as $student) {
        echo "  - {$student['nim']} - {$student['name']}\n";
    }
}
