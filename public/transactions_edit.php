<?php
require_once __DIR__ . '/../src/Config/init.php';

$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

$id = $_GET['id'] ?? 0;

// Fetch Transaction
$stmt = $db->getConnection()->prepare("SELECT * FROM transactions WHERE id = ?");
$stmt->execute([$id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    header('Location: transactions.php');
    exit;
}

// Access Control
if ($currentUser['role'] !== 'admin' && $transaction['created_by'] != $currentUser['id']) {
    header('Location: transactions.php');
    exit;
}

// Fetch helper data
$currencies = $db->getConnection()->query("SELECT * FROM currencies")->fetchAll();
$categories = $db->getConnection()->prepare("SELECT * FROM categories WHERE type = ?");
$categories->execute([$transaction['type']]);
$categories = $categories->fetchAll();

// Fetch students for dropdown
$students = $db->getConnection()->query("SELECT id, student_id_number, full_name FROM students WHERE status = 'active' ORDER BY full_name")->fetchAll();

$typeLabel = $transaction['type'] == 'income' ? 'Pemasukan' : 'Pengeluaran';
$typeColor = $transaction['type'] == 'income' ? 'success' : 'danger';
$typeIcon = $transaction['type'] == 'income' ? 'arrow-up-circle' : 'arrow-down-circle';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?= $typeLabel ?> - Sistem Kas Akademik</title>
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
                <!-- Header -->
                <div class="mb-4">
                    <a href="transactions.php" class="btn btn-light btn-sm mb-2">
                        <i class="bi bi-arrow-left"></i> Kembali ke List
                    </a>
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-<?= $typeColor ?> text-white me-3"
                            style="width: 60px; height: 60px; font-size: 1.8rem;">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-0">Edit <?= $typeLabel ?></h2>
                            <p class="text-muted mb-0">Perbarui data transaksi</p>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['flash'])): ?>
                    <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show shadow-sm"
                        role="alert">
                        <?= $_SESSION['flash']['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>

                <!-- Form Card -->
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form action="transactions_action.php" method="POST">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= $transaction['id'] ?>">

                            <!-- Preserve logic for type and currency handling simplistically for now -->

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-calendar3 text-primary me-1"></i>Tanggal
                                    </label>
                                    <input type="date" name="transaction_date" class="form-control"
                                        value="<?= date('Y-m-d', strtotime($transaction['transaction_date'])) ?>"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-tag text-primary me-1"></i>Kategori
                                    </label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($categories as $c): ?>
                                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $transaction['category_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label fw-medium">
                                    <i class="bi bi-card-text text-primary me-1"></i>Deskripsi
                                </label>
                                <input type="text" name="description" class="form-control"
                                    value="<?= htmlspecialchars($transaction['description']) ?>" required>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-cash-stack text-primary me-1"></i>Jumlah
                                        (<?= $transaction['currency_code'] ?>)
                                    </label>
                                    <input type="number" step="0.01" name="amount" id="amount" class="form-control"
                                        value="<?= $transaction['amount_original'] ?>" required
                                        oninput="updateConversion()">
                                    <input type="hidden" id="rate" value="<?= $transaction['exchange_rate_at_time'] ?>">
                                    <small class="text-muted">Rate saat transaksi:
                                        <?= number_format($transaction['exchange_rate_at_time'], 2) ?></small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-credit-card text-primary me-1"></i>Metode Pembayaran
                                    </label>
                                    <select name="payment_method" class="form-select" required>
                                        <?php
                                        $methods = ['cash' => 'Tunai (Cash)', 'bank_transfer' => 'Transfer Bank', 'e_wallet' => 'E-Wallet'];
                                        foreach ($methods as $val => $label):
                                            ?>
                                            <option value="<?= $val ?>" <?= $val == $transaction['payment_method'] ? 'selected' : '' ?>>
                                                <?= $label ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <?php if ($currentUser['role'] === 'admin' || $type === 'income'): ?>
                                <div class="mt-3">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-person text-primary me-1"></i>Mahasiswa/i (Opsional)
                                    </label>
                                    <select name="student_id" class="form-select">
                                        <option value="">Pilih Mahasiswa/i</option>
                                        <?php foreach ($students as $s): ?>
                                            <option value="<?= $s['id'] ?>" <?= $s['id'] == $transaction['student_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s['full_name']) ?>
                                                (<?= htmlspecialchars($s['student_id_number']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="mt-3">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block mb-1">Estimasi Konversi (IDR)</small>
                                                <h4 id="converted_preview" class="mb-0 text-primary fw-bold">Rp 0,00
                                                </h4>
                                            </div>
                                            <i class="bi bi-calculator fs-1 text-primary opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-lg flex-fill shadow-sm">
                                    <i class="bi bi-save me-2"></i>Simpan Perubahan
                                </button>
                                <a href="transactions.php" class="btn btn-light btn-lg">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateConversion() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const rate = parseFloat(document.getElementById('rate').value);
            const converted = amount * rate;

            const formatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            });
            document.getElementById('converted_preview').textContent = formatter.format(converted);
        }
        updateConversion();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
