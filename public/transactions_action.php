<?php
require_once __DIR__ . '/../src/Config/init.php';

$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? 0;

    if ($action === 'update') {
        $transaction_date = $_POST['transaction_date'];
        $category_id = $_POST['category_id'];
        $description = $_POST['description'];
        $amount = $_POST['amount'];
        $payment_method = $_POST['payment_method'];
        $student_id = !empty($_POST['student_id']) ? $_POST['student_id'] : null;


        $stmt = $db->getConnection()->prepare("SELECT exchange_rate_at_time FROM transactions WHERE id = ?");
        $stmt->execute([$id]);
        $rate = $stmt->fetchColumn();

        if ($rate) {
            $amount_base = $amount * $rate;

            $stmt = $db->getConnection()->prepare("
                UPDATE transactions 
                SET transaction_date = ?, category_id = ?, description = ?, 
                    amount_original = ?, amount_base = ?, payment_method = ?, student_id = ?
                WHERE id = ?
            ");

            try {

                $checkStmt = $db->getConnection()->prepare("SELECT created_by FROM transactions WHERE id = ?");
                $checkStmt->execute([$id]);
                $owner = $checkStmt->fetchColumn();

                if ($currentUser['role'] === 'admin' || $owner == $currentUser['id']) {
                    $stmt->execute([
                        $transaction_date,
                        $category_id,
                        $description,
                        $amount,
                        $amount_base,
                        $payment_method,
                        $student_id,
                        $id
                    ]);
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Transaksi berhasil diperbarui!'];
                } else {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Anda tidak memiliki akses untuk mengubah transaksi ini.'];
                }
            } catch (PDOException $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Gagal memperbarui transaksi: ' . $e->getMessage()];
            }
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Transaksi tidak ditemukan.'];
        }
        header('Location: transactions.php');
        exit;

    } elseif ($action === 'delete') {
        try {

            $checkStmt = $db->getConnection()->prepare("SELECT created_by FROM transactions WHERE id = ?");
            $checkStmt->execute([$id]);
            $owner = $checkStmt->fetchColumn();

            if ($currentUser['role'] === 'admin' || $owner == $currentUser['id']) {
                $stmt = $db->getConnection()->prepare("DELETE FROM transactions WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Transaksi berhasil dihapus!'];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Anda tidak memiliki akses untuk menghapus transaksi ini.'];
            }
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()];
        }
        header('Location: transactions.php');
        exit;
    }
}


header('Location: transactions.php');
exit;
