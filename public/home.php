<?php
require_once __DIR__ . '/../src/Config/init.php';


if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Uang Kas Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="landing-page">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-landing">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <div class="icon-box bg-primary text-white me-2" style="width: 40px; height: 40px; font-size: 1.2rem;">
                    <i class="bi bi-wallet2"></i>
                </div>
                <span class="fw-bold text-dark d-none d-sm-block">Sistem Uang Kas</span>
                <span class="fw-bold text-dark d-block d-sm-none">Kas Akademik</span>
            </a>
            <button class="navbar-toggler p-0 border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#beranda">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#fitur">Fitur</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a href="login.php" class="btn btn-primary px-4">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="beranda" class="hero-section d-flex align-items-center">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="hero-content">
                        <h1 class="display-4 fw-bold mb-4">Kelola Keuangan Akademik dengan <span
                                class="text-primary">Lebih Cerdas</span></h1>
                        <p class="lead text-muted mb-5">Platform digital terintegrasi untuk transparansi dan efisiensi
                            pengelolaan uang kas kelas, himpunan, dan organisasi akademik.</p>
                        <div class="d-flex gap-3">
                            <a href="login.php" class="btn btn-primary btn-lg px-4 shadow-lg">
                                Mulai Sekarang <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                        <div class="mt-5 d-flex gap-5 text-muted">
                            <div>
                                <h4 class="fw-bold mb-0 text-dark">100+</h4>
                                <small>Organisasi</small>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-dark">Rp 1M+</h4>
                                <small>Transaksi</small>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-dark">5k+</h4>
                                <small>Pengguna</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image-wrapper p-4 bg-white rounded-5 shadow-lg position-relative">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-primary opacity-10 rounded-5"
                            style="z-index: -1; transform: rotate(-3deg);"></div>
                        <div class="row g-4">
                            <div class="col-6">
                                <div class="p-4 bg-light rounded-4 h-100 border text-center">
                                    <div class="icon-box bg-success-subtle text-success mx-auto mb-3"
                                        style="width: 60px; height: 60px;">
                                        <i class="bi bi-graph-up-arrow fs-3"></i>
                                    </div>
                                    <h5 class="fw-bold">Laporan Real-time</h5>
                                    <p class="small text-muted mb-0">Pantau arus kas kapan saja dimana saja.</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-4 bg-light rounded-4 h-100 border text-center">
                                    <div class="icon-box bg-info-subtle text-info mx-auto mb-3"
                                        style="width: 60px; height: 60px;">
                                        <i class="bi bi-currency-exchange fs-3"></i>
                                    </div>
                                    <h5 class="fw-bold">Multi Mata Uang</h5>
                                    <p class="small text-muted mb-0">Konversi otomatis IDR, USD, dll.</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div
                                    class="p-4 bg-primary text-white rounded-4 shadow-sm d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="fw-bold mb-1">Transparansi Total</h5>
                                        <p class="small opacity-75 mb-0">Akses terbuka untuk semua anggota.</p>
                                    </div>
                                    <i class="bi bi-shield-check fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About/Features Section -->
    <section id="fitur" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mx-auto mb-5" style="max-width: 700px;">
                <span class="text-primary fw-bold text-uppercase letter-spacing-2">Kenapa Memilih Kami?</span>
                <h2 class="display-6 fw-bold mt-2">Solusi Lengkap untuk Kebutuhan Kas Anda</h2>
                <p class="text-muted mt-3">Tinggalkan cara catat manual. Beralih ke sistem digital yang aman,
                    transparan, dan mudah digunakan.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm hover-card p-4">
                        <div class="card-body">
                            <div class="icon-box bg-primary-subtle text-primary mb-4"
                                style="width: 60px; height: 60px; border-radius: 16px;">
                                <i class="bi bi-lightning-charge fs-3"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Cepat & Mudah</h4>
                            <p class="text-muted">Desain antarmuka yang intuitif memudahkan pencatatan pemasukan dan
                                pengeluaran hanya dalam beberapa klik.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm hover-card p-4">
                        <div class="card-body">
                            <div class="icon-box bg-success-subtle text-success mb-4"
                                style="width: 60px; height: 60px; border-radius: 16px;">
                                <i class="bi bi-file-earmark-spreadsheet fs-3"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Laporan Otomatis</h4>
                            <p class="text-muted">Generate laporan keuangan harian, bulanan, hingga tahunan secara
                                otomatis dan siap cetak atau ekspor.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm hover-card p-4">
                        <div class="card-body">
                            <div class="icon-box bg-warning-subtle text-warning mb-4"
                                style="width: 60px; height: 60px; border-radius: 16px;">
                                <i class="bi bi-shield-lock fs-3"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Aman & Terpercaya</h4>
                            <p class="text-muted">Data tersimpan aman di database dengan sistem autentikasi bertingkat
                                untuk admin dan pengguna.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-gradient-primary text-white text-center">
        <div class="container py-5">
            <h2 class="display-6 fw-bold mb-4">Siap Mengelola Kas dengan Lebih Baik?</h2>
            <p class="lead mb-5 opacity-90 mx-auto" style="max-width: 600px;">Bergabunglah dengan ribuan siswa dan
                mahasiswa yang telah beralih ke sistem digital.</p>
            <a href="login.php" class="btn btn-light btn-lg px-5 fw-bold text-primary shadow-lg">
                Masuk ke Sistem
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-primary text-white me-2"
                            style="width: 35px; height: 35px; font-size: 1rem;">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Sistem Uang Kas</h5>
                    </div>
                    <p class="text-secondary">Platform manajemen keuangan akademik terdepan untuk transparansi dan
                        akuntabilitas.</p>
                </div>
                <div class="col-lg-2 ms-auto">
                    <h6 class="fw-bold mb-3 text-uppercase small text-secondary dropdown-header">Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#beranda" class="text-white-50 text-decoration-none">Beranda</a></li>
                        <li class="mb-2"><a href="#fitur" class="text-white-50 text-decoration-none">Fitur</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="fw-bold mb-3 text-uppercase small text-secondary dropdown-header">Kontak</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="mailto:admin@akademiksistem.ac.id"
                                class="text-white-50 text-decoration-none"><i class="bi bi-envelope me-2"></i>
                                admin@akademiksistem.ac.id</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none"><i
                                    class="bi bi-whatsapp me-2"></i> - </a></li>
                    </ul>
                </div>
            </div>
            <div class="border-top border-secondary pt-3 text-center">
                <p class="small text-secondary mb-0">&copy; <?= date('Y') ?> Sistem Uang Kas Akademik. All rights
                    reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                document.querySelector('.navbar').classList.add('scrolled', 'shadow-sm', 'bg-white');
            } else {
                document.querySelector('.navbar').classList.remove('scrolled', 'shadow-sm', 'bg-white');
            }
        });
    </script>
</body>

</html>