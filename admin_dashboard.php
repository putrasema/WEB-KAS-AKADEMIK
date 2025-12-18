<?php
require_once 'config/init.php';

// Set timezone to Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');


$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

// Access Control: Allow everyone to see this dashboard layout (Global Stats & Currency)
// if ($currentUser['role'] !== 'admin') { ... } // Removed restricted access

// Get Admin Statistics
$monthlyIncome = $analytics->getMonthlyIncome(date('m'), date('Y'));
$monthlyExpense = $analytics->getMonthlyExpense(date('m'), date('Y'));

// Calculate total balance (all time)
$totalIncomeAllTime = $analytics->getTotalIncome();
$totalExpenseAllTime = $analytics->getTotalExpense();
$balance = $totalIncomeAllTime - $totalExpenseAllTime;

// Assign monthly values to variables used in view (Modified to use All Time as requested)
$totalIncome = $totalIncomeAllTime;
$totalExpense = $totalExpenseAllTime;

// Get total users
$totalUsers = $db->getConnection()->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
$totalStudents = $db->getConnection()->query("SELECT COUNT(*) as count FROM students WHERE status = 'active'")->fetch()['count'];
$totalTransactions = $db->getConnection()->query("SELECT COUNT(*) as count FROM transactions")->fetch()['count'];

// Recent transactions
$recentTransactions = $db->getConnection()->query("SELECT t.*, c.name as category_name, s.full_name as student_name, u.full_name as user_name FROM transactions t LEFT JOIN categories c ON t.category_id = c.id LEFT JOIN students s ON t.student_id = s.id LEFT JOIN users u ON t.created_by = u.id ORDER BY t.created_at DESC LIMIT 8")->fetchAll();

// Get monthly data for chart (last 6 months)
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('m', strtotime("-$i months"));
    $year = date('Y', strtotime("-$i months"));
    $monthlyData[] = [
        'month' => date('M Y', strtotime("-$i months")),
        'income' => $analytics->getMonthlyIncome($month, $year),
        'expense' => $analytics->getMonthlyExpense($month, $year)
    ];
}

// Get currency trend
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
    <title>Dashboard Admin - Sistem Kas Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .admin-badge {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            display: inline-block;
            font-weight: bold;
        }

        .stat-card-admin {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            background: var(--card-bg);
            box-shadow: 0 2px 10px rgba(252, 250, 250, 0.05);
        }

        .stat-card-admin:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .icon-box-admin {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 col-12 p-4 main-content">
                <?php include 'includes/mobile_header.php'; ?>
                <!-- Admin Header -->
                <div class="admin-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="admin-badge mb-2">
                                <i class="bi bi-shield-check me-2"></i>
                                <?= ucfirst($currentUser['role']) ?>
                            </div>
                            <h2 class="fw-bold mb-1">Dashboard</h2>
                            <p class="mb-0 opacity-75">Selamat datang,
                                <?= htmlspecialchars($currentUser['full_name']) ?>
                            </p>
                        </div>
                        <div>
                            <a href="transactions_add.php?type=income" class="btn btn-light me-2">
                                <i class="bi bi-plus-lg"></i> Pemasukan
                            </a>
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <a href="transactions_add.php?type=expense" class="btn btn-outline-light">
                                    <i class="bi bi-dash-lg"></i> Pengeluaran
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4 g-4">
                    <div class="col-md-3">
                        <div class="card stat-card-admin p-4 h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem;">Total
                                        Users</p>
                                    <h3 class="fw-bold mb-0"><?= $totalUsers ?></h3>
                                    <small class="text-success"><i class="bi bi-arrow-up"></i> Aktif</small>
                                </div>
                                <div class="icon-box-admin bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card-admin p-4 h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem;">
                                        Mahasiswa/i</p>
                                    <h3 class="fw-bold mb-0"><?= $totalStudents ?></h3>
                                    <small class="text-info"><i class="bi bi-person-check"></i> Aktif</small>
                                </div>
                                <div class="icon-box-admin bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card-admin p-4 h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem;">
                                        Total Transaksi</p>
                                    <h3 class="fw-bold mb-0"><?= $totalTransactions ?></h3>
                                    <small class="text-warning"><i class="bi bi-graph-up"></i> Transaksi</small>
                                </div>
                                <div class="icon-box-admin bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-receipt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card-admin p-4 h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem;">Saldo
                                        Kas</p>
                                    <h3 class="fw-bold mb-0 <?= $balance >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= formatCurrency($balance, 'IDR') ?>
                                    </h3>
                                    <small class="text-muted">Total Saldo</small>
                                </div>
                                <div class="icon-box-admin bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-wallet2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Overview -->
                <div class="row mb-4 g-4">
                    <div class="col-md-4">
                        <div class="card stat-card-admin p-4 h-100">
                            <h5 class="fw-bold mb-3">Total Pemasukan</h5>
                            <h2 class="text-success fw-bold"><?= formatCurrency($totalIncome, 'IDR') ?></h2>
                            <div class="progress mt-3" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: <?= $totalIncome > 0 ? 100 : 0 ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card-admin p-4 h-100">
                            <h5 class="fw-bold mb-3">Total Pengeluaran</h5>
                            <h2 class="text-danger fw-bold"><?= formatCurrency($totalExpense, 'IDR') ?></h2>
                            <div class="progress mt-3" style="height: 8px;">
                                <div class="progress-bar bg-danger" role="progressbar"
                                    style="width: <?= $totalExpense > 0 ? 100 : 0 ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card-admin p-4 h-100">
                            <h5 class="fw-bold mb-3">Selisih</h5>
                            <h2 class="<?= $balance >= 0 ? 'text-success' : 'text-danger' ?> fw-bold">
                                <?= formatCurrency(abs($balance), 'IDR') ?>
                            </h2>
                            <small class="text-muted"><?= $balance >= 0 ? 'Surplus' : 'Defisit' ?></small>
                        </div>
                    </div>
                </div>

                <!-- Chart & Recent Transactions -->
                <div class="row g-4">
                    <!-- Chart -->
                    <div class="col-md-7">
                        <div class="card stat-card-admin">
                            <div class="card-header border-0">
                                <h5 class="mb-0 fw-bold">Grafik Keuangan (6 Bulan Terakhir)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="financialChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="col-md-5">
                        <div class="card stat-card-admin h-100">
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <h5 class="mb-0 fw-bold">Transaksi Terbaru</h5>
                                <a href="transactions.php" class="btn btn-sm btn-light text-primary fw-bold">Lihat
                                    Semua</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach (array_slice($recentTransactions, 0, 6) as $t): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-bold"><?= htmlspecialchars($t['description']) ?>
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
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Currency Trend Chart -->
                <div class="row mb-4">
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
                                            $changeColor = $rateChange >= 0 ? '#8ab4f8' : '#f28b82'; // Blue for up, Red for down (Google Finance style)
                                            // Actually Google Finance uses Red for negative and Green for positive usually, but let's stick to standard or user image.
                                            // User image has Red graph, so likely rate dropped or it's just the style.
                                            // Let's use Red (#f28b82) for down and Green (#81c995) for up.
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
                                    <!-- Share/Follow buttons removed -->
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
                                                <!-- Custom arrow to match design if needed, or just let it be -->
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
                                            // 1 => '1HR', // Not available
                                            // 5 => '5HR', // Not available
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

                                    <!-- Tooltip Overlay (Custom HTML could be added here but Chart.js tooltip is easier) -->
                                </div>
                                <div class="mt-3 text-secondary small">
                                    <?= date('d M, H.i', strtotime('now')) ?> UTC · Dari Frankfurter API · Penafian
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4 g-4">
                    <div class="col-md-12">
                        <div class="card stat-card-admin">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3">Aksi Cepat</h5>
                                <div class="row g-3">
                                    <?php if ($currentUser['role'] === 'admin'): ?>
                                        <div class="col-md-3">
                                            <a href="students.php" class="btn btn-outline-primary w-100 py-3">
                                                <i class="bi bi-people-fill d-block mb-2" style="font-size: 2rem;"></i>
                                                Kelola Mahasiswa/i
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-md-3">
                                        <a href="transactions.php" class="btn btn-outline-success w-100 py-3">
                                            <i class="bi bi-receipt d-block mb-2" style="font-size: 2rem;"></i>
                                            Lihat Transaksi
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="analytics.php" class="btn btn-outline-warning w-100 py-3">
                                            <i class="bi bi-graph-up d-block mb-2" style="font-size: 2rem;"></i>
                                            Analitik
                                        </a>
                                    </div>
                                    <?php if ($currentUser['role'] === 'admin'): ?>
                                        <div class="col-md-3">
                                            <a href="register.php" class="btn btn-outline-info w-100 py-3">
                                                <i class="bi bi-person-plus-fill d-block mb-2" style="font-size: 2rem;"></i>
                                                Tambah User
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Financial Chart
        const ctx = document.getElementById('financialChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($monthlyData, 'month')) ?>,
                datasets: [{
                    label: 'Pemasukan',
                    data: <?= json_encode(array_column($monthlyData, 'income')) ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(49, 2, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Pengeluaran',
                    data: <?= json_encode(array_column($monthlyData, 'expense')) ?>,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Currency Chart
        const currencyCtx = document.getElementById('currencyChart').getContext('2d');
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
        // Using redish color from image for the line if it's main theme, or green/red based on trend.
        // Image shows Red line.
        const lineColor = isRateUp ? '#81c995' : '#f28b82'; // Green or Red

        let gradient = currencyCtx.createLinearGradient(0, 0, 0, 400);
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
                pointRadius: 0, // Hide points by default
                pointHoverRadius: 6,
                pointHoverBackgroundColor: lineColor,
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2,
                tension: 0, // Sharp lines
                fill: true
            }]
        };

        new Chart(currencyCtx, {
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
                        display: false // Hide legend
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
                            display: false, // Hide x grid
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 6,
                            color: '#9aa0a6'
                        }
                    },
                    y: {
                        position: 'right', // Axis on right
                        grid: {
                            color: '#3c4043', // Subtle grid lines
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
    </script>
</body>

</html>