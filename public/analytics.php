<?php
require_once __DIR__ . '/../src/Config/init.php';

$auth->requireLogin();

$breakdown = $analytics->getCategoryBreakdown('expense');
$advice = $analytics->getSavingsAdvice();

// Prepare data for charts
$labels = [];
$data = [];
foreach ($breakdown as $item) {
    $labels[] = $item['name'];
    $data[] = $item['total'];
}

// Get all-time data
$totalIncome = $analytics->getTotalIncome();
$totalExpense = $analytics->getTotalExpense();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analitik - Sistem Kas Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <a href="dashboard.php" class="btn btn-light btn-sm mb-2">
                            <i class="bi bi-arrow-left"></i> Kembali ke Dasbor
                        </a>
                        <h2 class="fw-bold mb-0">Analitik Keuangan</h2>
                        <p class="text-muted">Wawasan dan rekomendasi untuk pengelolaan keuangan yang lebih baik</p>
                    </div>
                    <div>
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <a href="export_analytics.php" class="btn btn-outline-success">
                                <i class="bi bi-file-earmark-spreadsheet me-2"></i>Download Laporan (CSV)
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="row mb-4 g-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.8rem;">
                                            Total Pemasukan</p>
                                        <h2 class="text-success fw-bold mb-0"><?= formatCurrency($totalIncome, 'IDR') ?>
                                        </h2>
                                    </div>
                                    <div class="icon-box bg-gradient-success text-white">
                                        <i class="bi bi-graph-up-arrow"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.8rem;">
                                            Total Pengeluaran</p>
                                        <h2 class="text-danger fw-bold mb-0"><?= formatCurrency($totalExpense, 'IDR') ?>
                                        </h2>
                                    </div>
                                    <div class="icon-box bg-gradient-danger text-white">
                                        <i class="bi bi-graph-down-arrow"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 fw-bold">
                                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                                    Rincian Pengeluaran Berdasarkan Kategori
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="expenseChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 fw-bold">
                                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                                    Kategori Pengeluaran Teratas
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($breakdown as $index => $item): ?>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center px-0 border-0">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary rounded-circle me-3"
                                                    style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                    <?= $index + 1 ?>
                                                </span>
                                                <span class="fw-medium"><?= htmlspecialchars($item['name']) ?></span>
                                            </div>
                                            <span class="badge bg-danger rounded-pill px-3 py-2">
                                                <?= formatCurrency($item['total'], 'IDR') ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('expenseChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Jumlah Pengeluaran (IDR)',
                    data: <?= json_encode($data) ?>,
                    backgroundColor: 'rgba(247, 37, 133, 0.8)',
                    borderColor: '#f72585',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>
