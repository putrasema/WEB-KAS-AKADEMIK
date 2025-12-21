<?php
require_once __DIR__ . '/../src/Config/init.php';

$auth->requireLogin();
$currentUser = $auth->getCurrentUser();


if ($currentUser['role'] !== 'admin') {
    die("Akses Ditolak: Hanya admin yang dapat mengunduh laporan.");
}


$filename = "laporan_keuangan_ska_" . date('Y-m-d') . ".csv";


header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');


$output = fopen('php://output', 'w');


fputcsv($output, [
    'ID Transaksi',
    'Tanggal',
    'Tipe',
    'Kategori',
    'Deskripsi',
    'Jumlah (IDR)',
    'Mata Uang Asal',
    'Jumlah Asal',
    'Rate',
    'Metode Pembayaran',
    'Dibuat Oleh'
]);



$sql = "
    SELECT 
        t.id, 
        t.transaction_date, 
        t.type, 
        c.name as category_name, 
        t.description, 
        t.amount_base, 
        t.currency_code, 
        t.amount_original, 
        t.exchange_rate_at_time, 
        t.payment_method, 
        u.full_name as created_by_name
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.id
    LEFT JOIN users u ON t.created_by = u.id
    ORDER BY t.transaction_date DESC
";

$stmt = $db->getConnection()->prepare($sql);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['id'],
        $row['transaction_date'],
        ucfirst($row['type']),
        $row['category_name'],
        $row['description'],
        $row['amount_base'],
        $row['currency_code'],
        $row['amount_original'],
        $row['exchange_rate_at_time'],
        ucfirst(str_replace('_', ' ', $row['payment_method'])),
        $row['created_by_name']
    ]);
}

fclose($output);
exit;
