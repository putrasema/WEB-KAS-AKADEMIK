<?php
require_once __DIR__ . '/../src/Config/init.php';

$auth->requireLogin();
$currentUser = $auth->getCurrentUser();


if ($currentUser['role'] !== 'admin') {
    header("Location: students.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: students.php");
    exit();
}


$stmt = $db->getConnection()->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: students.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id_number = $_POST['student_id_number'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $status = $_POST['status'];

    $stmt = $db->getConnection()->prepare("UPDATE students SET student_id_number = ?, full_name = ?, email = ?, phone = ?, status = ? WHERE id = ?");
    try {
        if ($stmt->execute([$student_id_number, $full_name, $email, $phone, $status, $id])) {
            $success = "Data mahasiswa berhasil diperbarui!";

            $stmt = $db->getConnection()->prepare("SELECT * FROM students WHERE id = ?");
            $stmt->execute([$id]);
            $student = $stmt->fetch();
        } else {
            $error = "Gagal memperbarui data.";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mahasiswa - Sistem Kas Akademik</title>
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

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <a href="students.php" class="btn btn-light btn-sm mb-2">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <h2 class="fw-bold mb-0">Edit Mahasiswa</h2>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm" style="max-width: 800px;">
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">NIM Mahasiswa</label>
                                <input type="text" name="student_id_number" class="form-control"
                                    value="<?= htmlspecialchars($student['student_id_number']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input type="text" name="full_name" class="form-control"
                                    value="<?= htmlspecialchars($student['full_name']) ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="<?= htmlspecialchars($student['email']) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Telepon</label>
                                    <input type="text" name="phone" class="form-control"
                                        value="<?= htmlspecialchars($student['phone']) ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= $student['status'] == 'active' ? 'selected' : '' ?>>Aktif
                                    </option>
                                    <option value="dropped_out" <?= $student['status'] == 'dropped_out' ? 'selected' : '' ?>>Keluar (Dropped Out)</option>
                                    <option value="graduated" <?= $student['status'] == 'graduated' ? 'selected' : '' ?>>
                                        Lulus</option>
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-2"></i> Simpan Perubahan
                                </button>
                                <a href="students.php" class="btn btn-light px-4">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>