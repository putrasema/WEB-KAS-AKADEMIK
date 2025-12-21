<?php
require_once __DIR__ . '/../src/Config/init.php';

$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

$type = $_GET['type'] ?? 'income';
$currencies = $db->getConnection()->query("SELECT * FROM currencies")->fetchAll();
$categories = $db->getConnection()->prepare("SELECT * FROM categories WHERE type = ?");
$categories->execute([$type]);
$categories = $categories->fetchAll();


if ($currentUser['role'] === 'student' && $type === 'expense') {
    header("Location: user_dashboard.php");
    exit;
}


$students = $db->getConnection()->query("SELECT id, student_id_number, full_name FROM students WHERE status = 'active' ORDER BY full_name")->fetchAll();


$currentStudent = null;
if ($currentUser['role'] === 'student') {
    $stmtStudent = $db->getConnection()->prepare("SELECT * FROM students WHERE full_name = ?");
    $stmtStudent->execute([$currentUser['full_name']]);
    $currentStudent = $stmtStudent->fetch();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $currencyCode = $_POST['currency'];
    $categoryId = $_POST['category_id'];
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'];
    $studentId = !empty($_POST['student_id']) ? $_POST['student_id'] : null;
    $paymentMethod = $_POST['payment_method'] ?? 'cash';


    $stmt = $db->getConnection()->prepare("SELECT exchange_rate FROM currencies WHERE code = ?");
    $stmt->execute([$currencyCode]);
    $rate = $stmt->fetchColumn();

    if ($rate) {
        $convertedAmount = $amount * $rate;

        $stmt = $db->getConnection()->prepare("INSERT INTO transactions (user_id, student_id, category_id, type, amount_original, currency_code, exchange_rate_at_time, amount_base, description, payment_method, transaction_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        try {
            $stmt->execute([
                $currentUser['id'],
                $studentId,
                $categoryId,
                $type,
                $amount,
                $currencyCode,
                $rate,
                $convertedAmount,
                $description,
                $paymentMethod,
                $date,
                $currentUser['id']
            ]);
            $success = "Transaksi berhasil ditambahkan!";
        } catch (PDOException $e) {
            $error = "Gagal menambahkan transaksi: " . $e->getMessage();
        }
    } else {
        $error = "Mata uang tidak valid.";
    }
}

$typeLabel = $type == 'income' ? 'Pemasukan' : 'Pengeluaran';
$typeColor = $type == 'income' ? 'success' : 'danger';
$typeIcon = $type == 'income' ? 'arrow-up-circle' : 'arrow-down-circle';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah <?= $typeLabel ?> - Sistem Kas Akademik</title>
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
                    <a href="dashboard.php" class="btn btn-light btn-sm mb-2">
                        <i class="bi bi-arrow-left"></i> Kembali ke Dasbor
                    </a>
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-<?= $typeColor ?> text-white me-3"
                            style="width: 60px; height: 60px; font-size: 1.8rem;">
                            <i class="bi bi-<?= $typeIcon ?>"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-0">Tambah <?= $typeLabel ?></h2>
                            <p class="text-muted mb-0">Catat transaksi <?= strtolower($typeLabel) ?> baru</p>
                        </div>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Form Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-<?= $typeColor ?> text-white">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Form Transaksi <?= $typeLabel ?></h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-calendar3 text-primary me-1"></i>Tanggal
                                    </label>
                                    <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-tag text-primary me-1"></i>Kategori
                                    </label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($categories as $c): ?>
                                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-cash-stack text-primary me-1"></i>Jumlah
                                    </label>
                                    <input type="number" step="0.01" name="amount" id="amount" class="form-control"
                                        placeholder="0.00" value="<?= $type == 'income' ? '30000' : '' ?>" required
                                        oninput="updateConversion()">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-currency-exchange text-primary me-1"></i>Mata Uang
                                    </label>
                                    <select name="currency" id="currency" class="form-select" required
                                        onchange="updateConversion()">
                                        <?php foreach ($currencies as $c): ?>
                                            <option value="<?= $c['code'] ?>" data-rate="<?= $c['exchange_rate'] ?>"
                                                <?= $c['code'] == 'IDR' ? 'selected' : '' ?>>
                                                <?= $c['code'] ?> - <?= $c['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label fw-medium">
                                    <i class="bi bi-credit-card text-primary me-1"></i>Metode Pembayaran
                                </label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="cash">Tunai (Cash)</option>
                                    <option value="bank_transfer">Transfer Bank</option>
                                    <option value="e_wallet">E-Wallet</option>
                                </select>
                            </div>

                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <div class="mt-3">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-person text-primary me-1"></i>Mahasiswa/i (Opsional)
                                    </label>
                                    <select name="student_id" class="form-select">
                                        <option value="">Pilih Mahasiswa/i</option>
                                        <?php foreach ($students as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?>
                                                (<?= htmlspecialchars($s['student_id_number']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php elseif ($type === 'income' && $currentStudent): ?>
                                <div class="mt-3">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-person text-primary me-1"></i>Mahasiswa/i
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-person-check text-success"></i>
                                        </span>
                                        <input type="text" class="form-control bg-light border-start-0"
                                            value="<?= htmlspecialchars($currentStudent['full_name']) ?> (<?= htmlspecialchars($currentStudent['student_id_number']) ?>)"
                                            readonly>
                                    </div>
                                    <input type="hidden" name="student_id" value="<?= $currentStudent['id'] ?>">
                                    <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Pemasukan
                                        ini akan otomatis dicatat atas nama Anda.</small>
                                </div>
                            <?php endif; ?>

                            <div class="mt-3">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block mb-1">Estimasi Konversi ke Rupiah
                                                    (IDR)</small>
                                                <h4 id="converted_preview" class="mb-0 text-primary fw-bold">Rp 0,00
                                                </h4>
                                            </div>
                                            <i class="bi bi-calculator fs-1 text-primary opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-<?= $typeColor ?> btn-lg flex-fill shadow-sm">
                                    <i class="bi bi-check-circle me-2"></i>Simpan Transaksi
                                </button>
                                <a href="dashboard.php" class="btn btn-light btn-lg">
                                    <i class="bi bi-x-circle"></i>
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
            const amountInput = document.getElementById('amount');
            const amount = parseFloat(amountInput.value) || 0;
            const currencySelect = document.getElementById('currency');
            const rate = parseFloat(currencySelect.options[currencySelect.selectedIndex].dataset.rate);
            const converted = amount * rate;

            const formatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            });
            document.getElementById('converted_preview').textContent = formatter.format(converted);
        }


        const isIncome = '<?= $type ?>' === 'income';
        const targetAmountIDR = 30000;

        document.getElementById('currency').addEventListener('change', function () {
            if (isIncome) {
                const currency = this.value;
                const rate = parseFloat(this.options[this.selectedIndex].dataset.rate);

                if (currency === 'USD') {
                    document.getElementById('amount').value = '1.81';
                } else if (currency === 'EUR') {
                    document.getElementById('amount').value = '1.56';
                } else if (currency === 'IDR') {
                    document.getElementById('amount').value = '30000';
                } else if (rate > 0) {
                    const requiredAmount = targetAmountIDR / rate;
                    document.getElementById('amount').value = requiredAmount.toFixed(2);
                }
                updateConversion();
            }
        });


        updateConversion();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>