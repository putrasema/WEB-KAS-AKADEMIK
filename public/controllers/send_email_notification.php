<?php
try {
    require_once __DIR__ . '/../../src/Config/init.php';

    header('Content-Type: application/json');


    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);


    if (!$auth->isLoggedIn()) {
        echo json_encode([
            'success' => false,
            'message' => 'Sesi Anda telah berakhir. Silakan login kembali.'
        ]);
        exit;
    }

    $currentUser = $auth->getCurrentUser();

    if ($currentUser['role'] !== 'admin') {
        echo json_encode([
            'success' => false,
            'message' => 'Akses ditolak. Hanya admin yang dapat mengirim notifikasi.'
        ]);
        exit;
    }


    $action = $_POST['action'] ?? '';
    $studentId = $_POST['student_id'] ?? null;


    $notificationService = new NotificationService();


    $currentMonth = date('m');
    $currentYear = date('Y');
    $monthName = date('F');
    $monthYear = $monthName . ' ' . $currentYear;


    $stmt = $db->getConnection()->prepare("SELECT id, name FROM categories WHERE name IN ('KAS-BULAN', 'SPP') ORDER BY FIELD(name, 'KAS-BULAN', 'SPP') LIMIT 1");
    $stmt->execute();
    $sppCategory = $stmt->fetch();
    $sppCategoryId = $sppCategory ? $sppCategory['id'] : null;
    $sppCategoryName = $sppCategory ? $sppCategory['name'] : 'Money Kas';


    $paymentLink = 'http://' . $_SERVER['HTTP_HOST'] . '/transactions_add.php?type=income';

    if ($action === 'send_single' && $studentId) {

        $stmt = $db->getConnection()->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();

        if (!$student) {
            echo json_encode([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan'
            ]);
            exit;
        }

        $result = $notificationService->sendPaymentReminder(
            $student,
            $sppCategoryName,
            $monthYear,
            $paymentLink
        );

        echo json_encode($result);

    } elseif ($action === 'send_bulk') {

        if (!$sppCategoryId) {
            echo json_encode([
                'success' => false,
                'message' => 'Kategori pembayaran tidak ditemukan'
            ]);
            exit;
        }


        $sql = "
            SELECT s.*, s.phone, s.email 
            FROM students s 
            WHERE s.status = 'active' 
            AND s.id NOT IN (
                SELECT student_id 
                FROM transactions 
                WHERE category_id = ? 
                AND MONTH(transaction_date) = ? 
                AND YEAR(transaction_date) = ?
                AND student_id IS NOT NULL
            )
            ORDER BY s.full_name ASC
        ";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$sppCategoryId, $currentMonth, $currentYear]);
        $unpaidStudents = $stmt->fetchAll();

        if (empty($unpaidStudents)) {
            echo json_encode([
                'success' => false,
                'message' => 'Tidak ada mahasiswa yang belum bayar'
            ]);
            exit;
        }

        $result = $notificationService->sendBulkPaymentReminders(
            $unpaidStudents,
            $sppCategoryName,
            $monthYear,
            $paymentLink
        );

        echo json_encode([
            'success' => true,
            'message' => "Email terkirim: {$result['success']} berhasil, {$result['failed']} gagal dari total {$result['total']} mahasiswa",
            'data' => $result
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Permintaan tidak valid'
        ]);
    }

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
    ]);
} catch (Error $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan fatal: ' . $e->getMessage()
    ]);
}
