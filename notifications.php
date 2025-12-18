<?php
require_once 'config/init.php';

$auth->requireLogin();
$currentUser = $auth->getCurrentUser();
$isAdmin = $currentUser['role'] === 'admin';

// Get Current Month and Year
$currentMonth = date('m');
$currentYear = date('Y');
$monthName = date('F');

// Get Monthly Fee Category ID (Prioritize 'KAS-BULAN', then 'SPP')
$stmt = $db->getConnection()->prepare("SELECT id, name FROM categories WHERE name IN ('KAS-BULAN', 'SPP') ORDER BY FIELD(name, 'KAS-BULAN', 'SPP') LIMIT 1");
$stmt->execute();
$sppCategory = $stmt->fetch();
$sppCategoryId = $sppCategory ? $sppCategory['id'] : null;
$sppCategoryName = $sppCategory ? $sppCategory['name'] : 'Money Kas';

$unpaidStudents = [];
$userPaymentStatus = 'unpaid'; // Default
$lastPaymentDate = null;

if ($isAdmin) {
    // Admin Logic: Find students who haven't paid SPP this month
    if ($sppCategoryId) {
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
    }
} else {
    // User Logic: Check if current user has paid
    // Ideally user is linked to student, but for now check by user_id
    if ($sppCategoryId) {
        $sql = "
            SELECT transaction_date 
            FROM transactions 
            WHERE user_id = ? 
            AND category_id = ? 
            AND MONTH(transaction_date) = ? 
            AND YEAR(transaction_date) = ?
            LIMIT 1
        ";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$currentUser['id'], $sppCategoryId, $currentMonth, $currentYear]);
        $payment = $stmt->fetch();

        if ($payment) {
            $userPaymentStatus = 'paid';
            $lastPaymentDate = $payment['transaction_date'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi & Pengingat - Sistem Kas Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .btn-email {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }

        .btn-email:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
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
                <!-- Header -->
                <div class="mb-4">
                    <h2 class="fw-bold mb-0">Notifikasi & Pengingat</h2>
                    <p class="text-muted">Status pembayaran <?= htmlspecialchars($sppCategoryName) ?> bulan
                        <?= $monthName ?> <?= $currentYear ?>
                    </p>
                </div>

                <?php if ($isAdmin): ?>
                    <!-- Admin View -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>Mahasiswa/i Belum Bayar
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($unpaidStudents)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">Luar Biasa!</h5>
                                    <p class="text-muted">Semua mahasiswa/i aktif sudah membayar uang kas bulan ini.</p>
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <button class="btn btn-email" id="sendBulkEmail">
                                        <i class="bi bi-envelope-fill me-2"></i>Kirim Email ke Semua
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>NIM</th>
                                                <th>Nama Lengkap</th>
                                                <th>Email</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($unpaidStudents as $student): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($student['student_id_number']) ?></td>
                                                    <td>
                                                        <div class="fw-bold"><?= htmlspecialchars($student['full_name']) ?></div>
                                                    </td>
                                                    <td>
                                                        <?php if ($student['email']): ?>
                                                            <i
                                                                class="bi bi-envelope me-1"></i><?= htmlspecialchars($student['email']) ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($student['email']): ?>
                                                            <button class="btn btn-sm btn-email send-email-btn"
                                                                data-student-id="<?= $student['id'] ?>"
                                                                data-student-name="<?= htmlspecialchars($student['full_name']) ?>">
                                                                <i class="bi bi-envelope-fill me-1"></i> Kirim Email
                                                            </button>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-secondary" disabled>
                                                                <i class="bi bi-envelope-x me-1"></i> No Email
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- User View -->
                    <?php if ($userPaymentStatus === 'paid'): ?>
                        <div class="alert alert-success shadow-sm border-0 d-flex align-items-center p-4">
                            <i class="bi bi-check-circle-fill fs-1 me-3"></i>
                            <div>
                                <h4 class="alert-heading fw-bold mb-1">Terima Kasih!</h4>
                                <p class="mb-0">Anda sudah membayar uang kas untuk bulan ini pada tanggal
                                    <?= date('d F Y', strtotime($lastPaymentDate)) ?>.
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card shadow-sm border-danger">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-exclamation-circle text-danger mb-3" style="font-size: 3rem;"></i>
                                <h3 class="fw-bold text-danger">Peringatan Pembayaran</h3>
                                <p class="lead mb-4">Anda belum membayar uang kas untuk bulan
                                    <strong><?= $monthName ?></strong>.
                                </p>
                                <a href="transactions_add.php?type=income" class="btn btn-danger btn-lg px-5 shadow">
                                    <i class="bi bi-wallet2 me-2"></i> Bayar Sekarang
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Send single email
        document.querySelectorAll('.send-email-btn').forEach(button => {
            button.addEventListener('click', function () {
                const studentId = this.getAttribute('data-student-id');
                const studentName = this.getAttribute('data-student-name');
                const originalHtml = this.innerHTML;

                if (!confirm(`Kirim email pengingat ke ${studentName}?`)) {
                    return;
                }

                // Show loading
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';

                // Send AJAX request
                const formData = new FormData();
                formData.append('action', 'send_single');
                formData.append('student_id', studentId);

                fetch('controllers/send_email_notification.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ ' + data.message);
                            this.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Terkirim';
                            this.classList.remove('btn-email');
                            this.classList.add('btn-success');
                        } else {
                            alert('❌ ' + data.message);
                            this.disabled = false;
                            this.innerHTML = originalHtml;
                        }
                    })
                    .catch(error => {
                        alert('❌ Terjadi kesalahan: ' + error);
                        this.disabled = false;
                        this.innerHTML = originalHtml;
                    });
            });
        });

        // Send bulk email
        document.getElementById('sendBulkEmail')?.addEventListener('click', function () {
            const totalStudents = document.querySelectorAll('.send-email-btn').length;

            if (!confirm(`Kirim email pengingat ke semua ${totalStudents} mahasiswa yang belum bayar?`)) {
                return;
            }

            const originalHtml = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim email...';

            const formData = new FormData();
            formData.append('action', 'send_bulk');

            fetch('controllers/send_email_notification.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + data.message);
                        location.reload(); // Reload to update button states
                    } else {
                        alert('❌ ' + data.message);
                        this.disabled = false;
                        this.innerHTML = originalHtml;
                    }
                })
                .catch(error => {
                    alert('❌ Terjadi kesalahan: ' + error);
                    this.disabled = false;
                    this.innerHTML = originalHtml;
                });
        });
    </script>
</body>

</html>