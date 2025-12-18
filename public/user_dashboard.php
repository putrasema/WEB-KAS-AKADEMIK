<?php
require_once __DIR__ . '/../src/Config/init.php';

$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

// Access Control: Allow everyone to see this dashboard layout (Transaction focused)
// if ($currentUser['role'] === 'admin') { ... } // Removed restricted access

// Get User Statistics
// Get User Statistics
$monthlyIncome = $analytics->getMonthlyIncome(date('m'), date('Y'));
$monthlyExpense = $analytics->getMonthlyExpense(date('m'), date('Y'));

// Calculate total balance (all time)
$totalIncomeAllTime = $analytics->getTotalIncome();
$totalExpenseAllTime = $analytics->getTotalExpense();
$balance = $totalIncomeAllTime - $totalExpenseAllTime;

// Assign monthly values to variables used in view (Modified to use All Time as requested)
$totalIncome = $totalIncomeAllTime;
$totalExpense = $totalExpenseAllTime;

// Get user's recent transactions (created by this user)
$stmt = $db->getConnection()->prepare("SELECT t.*, c.name as category_name, s.full_name as student_name FROM transactions t LEFT JOIN categories c ON t.category_id = c.id LEFT JOIN students s ON t.student_id = s.id WHERE t.created_by = ? ORDER BY t.transaction_date DESC LIMIT 5");
$stmt->execute([$currentUser['id']]);
$myTransactions = $stmt->fetchAll();

// Get all recent transactions
$recentTransactions = $db->getConnection()->query("SELECT t.*, c.name as category_name, s.full_name as student_name FROM transactions t LEFT JOIN categories c ON t.category_id = c.id LEFT JOIN students s ON t.student_id = s.id ORDER BY t.transaction_date DESC LIMIT 5")->fetchAll();

// Get currency trend (Added for User Dashboard)
$currencyRange = isset($_GET['currency_range']) ? (int) $_GET['currency_range'] : 30; // Default 30 days
$currencySource = isset($_GET['currency_source']) ? $_GET['currency_source'] : 'USD'; // Default USD
$validSources = ['USD' => 'Dolar Amerika Serikat', 'EUR' => 'Euro'];
if (!array_key_exists($currencySource, $validSources)) {
    $currencySource = 'USD';
}

$currencyTrend = $currencyService->getTrend($currencySource, 'IDR', $currencyRange);

// Get current rate (last item in trend)
$currentRate = !empty($currencyTrend) ? end($currencyTrend)['rate'] : 0;
$previousRate = !empty($currencyTrend) && count($currencyTrend) > 1 ? $currencyTrend[count($currencyTrend) - 2]['rate'] : $currentRate;
$rateChange = $currentRate - $previousRate;
$isRateUp = $rateChange >= 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Kas Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .user-header {
            background: linear-gradient(135deg, #4cc9f0 0%, #4361ee 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .user-badge {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            display: inline-block;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include __DIR__ . '/../src/Includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 col-12 p-4 main-content">
                <?php include __DIR__ . '/../src/Includes/mobile_header.php'; ?>
                <!-- User Header -->
                <div class="user-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="user-badge mb-2">
                                <i class="bi bi-person-circle me-2"></i><?= ucfirst($currentUser['role']) ?>
                            </div>
                            <h2 class="fw-bold mb-1">Dashboard</h2>
                            <p class="mb-0 opacity-75">Selamat datang kembali,
                                <?= htmlspecialchars($currentUser['full_name']) ?>
                            </p>
                        </div>
                        <div>
                            <a href="transactions_add.php?type=income" class="btn btn-light me-2">
                                <i class="bi bi-plus-lg"></i> Pemasukan
                            </a>
                            <a href="transactions_add.php?type=expense" class="btn btn-outline-light">
                                <i class="bi bi-dash-lg"></i> Pengeluaran
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4 g-4">
                    <div class="col-md-4">
                        <div class="card stat-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.8rem;">Saldo
                                        Saat Ini</p>
                                    <h2 class="fw-bold mb-0"><?= formatCurrency($balance, 'IDR') ?></h2>
                                </div>
                                <div class="icon-box bg-gradient-primary text-white shadow-sm">
                                    <i class="bi bi-wallet2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.8rem;">
                                        Total Pemasukan</p>
                                    <h2 class="text-success fw-bold mb-0"><?= formatCurrency($totalIncome, 'IDR') ?>
                                    </h2>
                                </div>
                                <div class="icon-box bg-gradient-success text-white shadow-sm">
                                    <i class="bi bi-arrow-down-left"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.8rem;">
                                        Total Pengeluaran</p>
                                    <h2 class="text-danger fw-bold mb-0"><?= formatCurrency($totalExpense, 'IDR') ?>
                                    </h2>
                                </div>
                                <div class="icon-box bg-gradient-danger text-white shadow-sm">
                                    <i class="bi bi-arrow-up-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- My Recent Transactions -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">Transaksi Saya</h5>
                                <span class="badge bg-primary"><?= count($myTransactions) ?> Transaksi</span>
                            </div>
                            <div class="card-body p-0">
                                <?php if (count($myTransactions) > 0): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($myTransactions as $t): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 fw-bold">
                                                            <?= htmlspecialchars($t['description']) ?>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar3"></i>
                                                            <?= date('d M Y', strtotime($t['transaction_date'])) ?>
                                                            <?php if ($t['student_name']): ?>
                                                                | <i class="bi bi-person"></i>
                                                                <?= htmlspecialchars($t['student_name']) ?>
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                    <span
                                                        class="badge <?= $t['type'] == 'income' ? 'bg-success' : 'bg-danger' ?> ms-2">
                                                        <?= $t['type'] == 'income' ? '+' : '-' ?>
                                                        <?= formatCurrency($t['amount_base'], 'IDR') ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-3">Belum ada transaksi</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- All Recent Transactions -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">Transaksi Terbaru</h5>
                                <a href="transactions.php" class="btn btn-sm btn-light text-primary fw-bold">Lihat
                                    Semua</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4">Tanggal</th>
                                                <th>Deskripsi</th>
                                                <th class="text-end pe-4">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentTransactions as $t): ?>
                                                <tr>
                                                    <td class="ps-4 text-muted">
                                                        <?= date('d M', strtotime($t['transaction_date'])) ?>
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium"><?= htmlspecialchars($t['description']) ?>
                                                        </div>
                                                        <?php if ($t['student_name']): ?>
                                                            <small class="text-muted">
                                                                <i class="bi bi-person"></i>
                                                                <?= htmlspecialchars($t['student_name']) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <span
                                                            class="<?= $t['type'] == 'income' ? 'text-success' : 'text-danger' ?> fw-bold">
                                                            <?= $t['type'] == 'income' ? '+' : '-' ?>
                                                            <?= formatCurrency($t['amount_base'], 'IDR') ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="row mt-4 g-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0 fw-bold">Pemasukan vs Pengeluaran</h5>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <div style="width: 100%; max-width: 300px;">
                                    <canvas id="pieChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Info -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0 fw-bold">Informasi Cepat</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3 p-3 border rounded">
                                    <div class="icon-box bg-primary text-white me-3">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Bulan Ini</h6>
                                        <small class="text-muted"><?= date('F Y') ?></small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3 p-3 border rounded">
                                    <div class="icon-box bg-success text-white me-3">
                                        <i class="bi bi-graph-up"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Status Keuangan</h6>
                                        <small
                                            class="<?= $balance >= 0 ? 'text-success' : 'text-danger' ?> fw-bold"><?= $balance >= 0 ? 'Surplus' : 'Defisit' ?></small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="icon-box bg-info text-white me-3">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Role Anda</h6>
                                        <small class="text-muted"><?= ucfirst($currentUser['role']) ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Currency Trend Chart (Added for User) -->
                <div class="row mt-4 mb-4">
                    <div class="col-12">
                        <!-- Dark Mode Currency Card -->
                        <div class="card stat-card-admin"
                            style="background-color: #1b2136ff; color: #ffffffff; border: 1px solid #3c4043;">
                            <div class="card-body p-4">
                                <!-- Currency Header -->
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-0 text-white fw-normal fs-6">1
                                            <?= $validSources[$currencySource] ?> sama
                                            dengan
                                        </h5>
                                        <h1 class="display-5 fw-bold mb-0 text-white mt-1">
                                            <?= number_format($currentRate, 2, ',', '.') ?> <span
                                                class="fs-4 fw-normal text-white">Rupiah Indonesia</span>
                                        </h1>
                                        <div class="mt-2">
                                            <?php
                                            $changeColor = $rateChange >= 0 ? '#8ab4f8' : '#f28b82';
                                            $trendColor = $isRateUp ? '#81c995' : '#f28b82';
                                            $trendIcon = $isRateUp ? '+' : '';
                                            ?>
                                            <span style="color: <?= $trendColor ?>; font-weight: bold;">
                                                <?= $trendIcon ?><?= number_format($rateChange, 2, ',', '.') ?>
                                                (<?= number_format(($rateChange / $previousRate) * 100, 2, ',', '.') ?>%)
                                            </span>
                                            <span class="text-white small ms-1">Hari ini</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Converters -->
                                <div class="row g-3 my-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center bg-dark border border-secondary rounded p-2"
                                            style="background-color: #303134 !important; border-color: #3c4043 !important;">
                                            <input type="number" id="inputSource"
                                                class="form-control bg-transparent border-0 text-white fw-bold shadow-none"
                                                value="1">
                                            <div class="border-start border-secondary ps-2">
                                                <select
                                                    class="form-select bg-transparent border-0 text-white fw-bold shadow-none py-0 pe-5"
                                                    style="cursor: pointer; color-scheme: dark;"
                                                    onchange="window.location.href='?currency_source=' + this.value">
                                                    <?php foreach ($validSources as $code => $name): ?>
                                                        <option value="<?= $code ?>" <?= $currencySource == $code ? 'selected' : '' ?> 
                                                            style="background-color: #202124; color: #ffffff;">
                                                            <?= $name ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center bg-dark border border-secondary rounded p-2"
                                            style="background-color: #303134 !important; border-color: #3c4043 !important;">
                                            <input type="text" id="inputTarget"
                                                class="form-control bg-transparent border-0 text-white fw-bold shadow-none"
                                                value="<?= number_format($currentRate, 2, ',', '.') ?>">
                                            <span class="text-white pe-3 border-start border-secondary ps-3">Rupiah
                                                Indonesia</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chart Section -->
                                <div class="position-relative">
                                    <!-- Time Toggles -->
                                    <div class="d-flex justify-content-end mb-3 gap-1">
                                        <?php
                                        $ranges = [
                                            30 => '1BLN',
                                            180 => '6BLN',
                                            365 => '1TH',
                                            1825 => '5TH'
                                        ];
                                        foreach ($ranges as $days => $label):
                                            $isActive = $currencyRange == $days;
                                            $activeClass = $isActive ? 'bg-secondary text-white' : 'text-white-50 hover-bg-dark';
                                            ?>
                                            <a href="?currency_range=<?= $days ?>"
                                                class="btn btn-sm rounded-pill px-3 fw-bold <?= $activeClass ?>"
                                                style="font-size: 0.8rem; <?= $isActive ? 'background-color: #8ab4f8 !important; color: #202124 !important;' : '' ?>">
                                                <?= $label ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Chart -->
                                    <div style="height: 350px; width: 100%;">
                                        <canvas id="currencyChart"></canvas>
                                    </div>
                                    
                                    <div class="mt-3 text-secondary small">
                                        <?= date('d M, H.i', strtotime('now')) ?> UTC · Dari Frankfurter API · Penafian
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3">Aksi Cepat</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <a href="transactions.php" class="btn btn-outline-primary w-100 py-3">
                                            <i class="bi bi-receipt d-block mb-2" style="font-size: 2rem;"></i>
                                            Lihat Semua Transaksi
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="students.php" class="btn btn-outline-info w-100 py-3">
                                            <i class="bi bi-people-fill d-block mb-2" style="font-size: 2rem;"></i>
                                            Data Mahasiswa/i
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="analytics.php" class="btn btn-outline-success w-100 py-3">
                                            <i class="bi bi-graph-up d-block mb-2" style="font-size: 2rem;"></i>
                                            Lihat Analitik
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pie Chart
        const ctx = document.getElementById('pieChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pemasukan', 'Pengeluaran'],
                datasets: [{
                    data: [<?= $totalIncome ?>, <?= $totalExpense ?>],
                    backgroundColor: ['#10b981', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Currency Chart (Added for User)
        // Ensure variables are defined safely
        const currencyCtx = document.getElementById('currencyChart');
        if (currencyCtx) {
            const ctxCurrency = currencyCtx.getContext('2d');
            const currentRate = <?= $currentRate ?>;
            const inputSource = document.getElementById('inputSource');
            const inputTarget = document.getElementById('inputTarget');
            const isRateUp = <?= $isRateUp ? 'true' : 'false' ?>;

            // Helper to format number
            const formatIDR = (num) => {
                return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(num);
            };
            const parseIDR = (str) => {
                return parseFloat(str.replace(/\./g, '').replace(',', '.'));
            };

            // Live Conversion
            inputSource.addEventListener('input', function (e) {
                const val = parseFloat(e.target.value);
                if (!isNaN(val)) {
                    inputTarget.value = formatIDR(val * currentRate);
                } else {
                    inputTarget.value = '';
                }
            });

            inputTarget.addEventListener('change', function (e) {
                const val = parseIDR(e.target.value);
                if (!isNaN(val)) {
                    inputSource.value = (val / currentRate).toFixed(4);
                    // Reformat target to look nice
                    e.target.value = formatIDR(val);
                }
            });

            // Gradient & Colors
            const lineColor = isRateUp ? '#81c995' : '#f28b82'; // Green or Red

            let gradient = ctxCurrency.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, isRateUp ? 'rgba(129, 201, 149, 0.2)' : 'rgba(242, 139, 130, 0.2)');
            gradient.addColorStop(1, 'rgba(32, 33, 36, 0)');   // Fade to dark bg

            const currencyData = {
                labels: <?= json_encode(array_column($currencyTrend ?? [], 'date')) ?>,
                datasets: [{
                    label: 'Kurs',
                    data: <?= json_encode(array_column($currencyTrend ?? [], 'rate')) ?>,
                    borderColor: lineColor,
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: lineColor,
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                    tension: 0,
                    fill: true
                }]
            };

            new Chart(ctxCurrency, {
                type: 'line',
                data: currencyData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#3c4043',
                            titleColor: '#e8eaed',
                            bodyColor: '#e8eaed',
                            borderColor: '#5f6368',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                label: function (context) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 6,
                                color: '#9aa0a6'
                            }
                        },
                        y: {
                            position: 'right',
                            grid: {
                                color: '#3c4043',
                                borderDash: [5, 5],
                                drawBorder: false
                            },
                            ticks: {
                                color: '#9aa0a6',
                                callback: function (value) {
                                    return value.toLocaleString('id-ID');
                                }
                            },
                            border: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>

</html>
