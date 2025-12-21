<?php
require_once __DIR__ . '/../src/Config/init.php';

$auth->requireLogin();
$currentUser = $auth->getCurrentUser();


$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;


$whereClause = "";
$params = [];

if ($currentUser['role'] === 'student') {

    $stmtStudent = $db->getConnection()->prepare("SELECT id FROM students WHERE full_name = ?");
    $stmtStudent->execute([$currentUser['full_name']]);
    $studentId = $stmtStudent->fetchColumn();

    if ($studentId) {

        $whereClause = "WHERE t.student_id = ?";
        $params = [$studentId];
    } else {

        $whereClause = "WHERE t.created_by = ?";
        $params = [$currentUser['id']];
    }
}


$sql = "
    SELECT t.*, c.name as category_name, s.full_name as student_name
    FROM transactions t 
    LEFT JOIN categories c ON t.category_id = c.id 
    LEFT JOIN students s ON t.student_id = s.id
    $whereClause
    ORDER BY t.transaction_date DESC 
    LIMIT ? OFFSET ?
";

$stmt = $db->getConnection()->prepare($sql);
$paramIndex = 1;
foreach ($params as $param) {
    $stmt->bindValue($paramIndex++, $param);
}
$stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
$stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll();

$countSql = "SELECT COUNT(*) FROM transactions t $whereClause";
$stmtCount = $db->getConnection()->prepare($countSql);
$paramIndex = 1;
foreach ($params as $param) {
    $stmtCount->bindValue($paramIndex++, $param);
}
$stmtCount->execute();
$total = $stmtCount->fetchColumn();
$totalPages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Sistem Kas Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include __DIR__ . '/../src/Includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 col-12 p-4 main-content">
                <?php include __DIR__ . '/../src/Includes/mobile_header.php'; ?>
                <!-- Header with Back Button -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <a href="dashboard.php" class="btn btn-light btn-sm mb-2">
                            <i class="bi bi-arrow-left"></i> Kembali ke Dasbor
                        </a>
                        <h2 class="fw-bold mb-0">Semua Transaksi</h2>
                        <p class="text-muted">Kelola pemasukan dan pengeluaran Anda</p>
                    </div>
                    <div>
                        <a href="transactions_add.php?type=income" class="btn btn-success shadow-sm me-2">
                            <i class="bi bi-plus-circle"></i> Tambah Pemasukan
                        </a>
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <a href="transactions_add.php?type=expense" class="btn btn-danger shadow-sm">
                                <i class="bi bi-dash-circle"></i> Tambah Pengeluaran
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Transactions Table Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-list-ul text-primary me-2"></i>Daftar Transaksi
                            </h5>
                            <span class="badge bg-primary rounded-pill"><?= $total ?> Total</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Tanggal</th>
                                        <th>Mahasiswa/i</th>
                                        <th>Kategori</th>
                                        <th>Metode Pembayaran</th>
                                        <th>Jumlah Asli</th>
                                        <th>Jumlah (IDR)</th>
                                        <th>Tipe</th>
                                        <?php if ($currentUser['role'] !== 'student'): ?>
                                            <th class="text-center pe-4">Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $t): ?>
                                        <tr>
                                            <td class="ps-4 text-muted fw-medium">
                                                <?= date('d M Y', strtotime($t['transaction_date'])) ?>
                                            </td>

                                            <td>
                                                <?php if ($t['student_name']): ?>
                                                    <span class="badge bg-info text-dark">
                                                        <i
                                                            class="bi bi-person-fill me-1"></i><?= htmlspecialchars($t['student_name']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($t['category_name'] ?? '-') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $paymentIcons = [
                                                    'cash' => 'cash-coin',
                                                    'bank_transfer' => 'bank',
                                                    'credit_card' => 'credit-card',
                                                    'debit_card' => 'credit-card-2-front',
                                                    'e_wallet' => 'wallet2'
                                                ];
                                                $paymentLabels = [
                                                    'cash' => 'Tunai',
                                                    'bank_transfer' => 'Transfer Bank',
                                                    'credit_card' => 'Kartu Kredit',
                                                    'debit_card' => 'Kartu Debit',
                                                    'e_wallet' => 'E-Wallet'
                                                ];
                                                $icon = $paymentIcons[$t['payment_method']] ?? 'cash-coin';
                                                $label = $paymentLabels[$t['payment_method']] ?? ucfirst($t['payment_method']);
                                                ?>
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-<?= $icon ?> me-1"></i><?= $label ?>
                                                </span>
                                            </td>
                                            <td class="text-muted">
                                                <?= $t['currency_code'] . ' ' . number_format($t['amount_original'], 2) ?>
                                            </td>
                                            <td class="fw-bold"><?= formatCurrency($t['amount_base'], 'IDR') ?></td>
                                            <td>
                                                <span
                                                    class="badge bg-<?= $t['type'] == 'income' ? 'success' : 'danger' ?> rounded-pill">
                                                    <i
                                                        class="bi bi-<?= $t['type'] == 'income' ? 'arrow-up' : 'arrow-down' ?>"></i>
                                                    <?= ucfirst($t['type']) ?>
                                                </span>
                                            </td>
                                            <?php if ($currentUser['role'] !== 'student'): ?>
                                                <td class="text-center pe-4">
                                                    <a href="transactions_edit.php?id=<?= $t['id'] ?>"
                                                        class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="transactions_action.php" method="POST" class="d-inline"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger ms-1"
                                                            title="Hapus">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="card-footer bg-white border-top">
                            <nav>
                                <ul class="pagination justify-content-center mb-0">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>