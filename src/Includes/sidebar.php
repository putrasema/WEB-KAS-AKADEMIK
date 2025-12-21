<!-- includes/sidebar.php -->
<div class="col-md-2 sidebar p-0 d-flex flex-column">
    <div class="brand d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-0"><i class="bi bi-wallet2 text-info me-2"></i>SKA</h4>
            <small class="text-white-50" style="font-size: 0.8rem;">Sistem Kas Akademik</small>
        </div>
        <button class="btn btn-link text-white d-md-none p-0" onclick="toggleSidebar()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <nav class="mt-2 flex-grow-1">
        <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
        $currentUser = $auth->getCurrentUser();
        $isAdmin = $currentUser['role'] === 'admin';
        ?>

        <!-- User Info -->
        <div class="px-3 mb-3">
            <div class="d-flex align-items-center p-2 rounded" style="background: rgba(255,255,255,0.1);">
                <div class="icon-box bg-white text-primary me-2" style="width: 35px; height: 35px; font-size: 0.9rem;">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="text-white fw-bold text-truncate" style="font-size: 0.85rem;">
                        <?= htmlspecialchars($currentUser['full_name']) ?>
                    </div>
                    <small class="text-white-50" style="font-size: 0.7rem;">
                        <?= $isAdmin ? '👑 Admin' : '👤 ' . ucfirst($currentUser['role']) ?>
                    </small>
                </div>
            </div>
        </div>

        <a href="dashboard.php"
            class="<?= in_array($currentPage, ['dashboard.php', 'admin_dashboard.php', 'user_dashboard.php']) ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i> Dasbor
        </a>
        <a href="transactions.php" class="<?= strpos($currentPage, 'transactions') !== false ? 'active' : '' ?>">
            <i class="bi bi-arrow-left-right"></i> Transaksi
        </a>
        <?php if ($isAdmin): ?>
            <a href="students.php" class="<?= $currentPage == 'students.php' ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i> Mahasiswa/i
            </a>
        <?php endif; ?>
        <a href="analytics.php" class="<?= $currentPage == 'analytics.php' ? 'active' : '' ?>">
            <i class="bi bi-graph-up-arrow"></i> Analitik
        </a>
        <a href="notifications.php" class="<?= $currentPage == 'notifications.php' ? 'active' : '' ?>">
            <i class="bi bi-bell-fill"></i> Notifikasi
        </a>

        <?php if ($isAdmin): ?>
            <hr class="my-3 mx-3 border-white-50">
            <div class="px-3 mb-2">
                <small class="text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Admin Menu</small>
            </div>
            <a href="register.php" class="<?= $currentPage == 'register.php' ? 'active' : '' ?>">
                <i class="bi bi-person-plus-fill"></i> Kelola User
            </a>
        <?php endif; ?>
    </nav>

    <div class="mt-auto pt-3 px-3 pb-4">
        <button id="theme-toggle"
            class="btn btn-outline-light w-100 d-flex justify-content-between align-items-center mb-3"
            style="border: 1px solid rgba(255,255,255,0.2);">
            <span>Mode Tampilan</span>
            <i class="bi bi-moon-stars-fill theme-icon"></i>
        </button>
        <a href="logout.php" class="btn btn-danger w-100 d-flex justify-content-center align-items-center text-white"
            style="border-radius: 12px;">
            <i class="bi bi-box-arrow-right me-2"></i> Keluar
        </a>
    </div>
</div>
<script src="assets/js/theme.js"></script>