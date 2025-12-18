<?php
require_once __DIR__ . '/../src/Config/init.php';

$auth->requireLogin();
$currentUser = $auth->getCurrentUser();
$isAdmin = ($currentUser['role'] === 'admin');

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isAdmin) {
        $error = "Akses ditolak. Hanya admin yang dapat melakukan tindakan ini.";
    } elseif (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $stmt = $db->getConnection()->prepare("INSERT INTO students (student_id_number, full_name, email, phone, status) VALUES (?, ?, ?, ?, ?)");
            try {
                $stmt->execute([
                    $_POST['student_id_number'],
                    $_POST['full_name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['status']
                ]);
                $success = "Mahasiswa/i berhasil ditambahkan!";
            } catch (PDOException $e) {
                $error = "Error menambahkan mahasiswa/i: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $db->getConnection()->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $success = "Mahasiswa/i berhasil dihapus!";
        }
    }
}

$students = $db->getConnection()->query("SELECT * FROM students ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mahasiswa/i - Sistem Kas Akademik</title>
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
                        <h2 class="fw-bold mb-0">Manajemen Mahasiswa/i</h2>
                        <p class="text-muted">Kelola data mahasiswa/i akademik</p>
                    </div>
                    <?php if ($isAdmin): ?>
                        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                            <i class="bi bi-person-plus"></i> Tambah Mahasiswa/i Baru
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Success/Error Messages -->
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Students Grid -->
                <div class="row g-4">
                    <?php foreach ($students as $s): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm hover-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="icon-box bg-gradient-primary text-white"
                                            style="width: 50px; height: 50px;">
                                            <i class="bi bi-person-badge"></i>
                                        </div>
                                        <span
                                            class="badge bg-<?= $s['status'] == 'active' ? 'success' : 'secondary' ?> rounded-pill">
                                            <?= ucfirst($s['status']) ?>
                                        </span>
                                    </div>
                                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($s['full_name']) ?></h5>
                                    <p class="text-muted mb-3">
                                        <i class="bi bi-credit-card-2-front me-1"></i>
                                        <?= htmlspecialchars($s['student_id_number']) ?>
                                    </p>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-envelope me-1"></i>
                                            <?= htmlspecialchars($s['email'] ?: 'Tidak ada email') ?>
                                        </small>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="bi bi-telephone me-1"></i>
                                            <?= htmlspecialchars($s['phone'] ?: 'Tidak ada telepon') ?>
                                        </small>
                                    </div>
                                    <?php if ($isAdmin): ?>
                                        <div class="d-flex gap-2">
                                            <a href="students_edit.php?id=<?= $s['id'] ?>"
                                                class="btn btn-sm btn-outline-primary flex-fill">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <form method="POST" style="display:inline;" class="flex-fill">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                                                    onclick="return confirm('Apakah Anda yakin?')">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <?php if ($isAdmin): ?>
        <div class="modal fade" id="addStudentModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Tambah Mahasiswa/i Baru</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label class="form-label fw-medium">NIM Mahasiswa/i</label>
                                <input type="text" name="student_id_number" class="form-control" placeholder="contoh: 01234"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Nama Lengkap</label>
                                <input type="text" name="full_name" class="form-control" placeholder="masukan nama lengkap"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="mahasiswa@example.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Telepon</label>
                                <input type="text" name="phone" class="form-control" placeholder="+62 xxx xxxx xxxx">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Aktif</option>
                                    <option value="dropped_out">Keluar</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Simpan Mahasiswa/i
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
