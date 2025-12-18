<div class="mobile-header d-md-none">
    <div class="d-flex align-items-center">
        <button class="btn btn-link text-dark p-0 me-3" onclick="toggleSidebar()">
            <i class="bi bi-list fs-1 text-dark"></i>
        </button>
        <div>
            <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-wallet2 me-2"></i>SKA</h5>
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <button id="theme-toggle-mobile" class="btn btn-link text-dark p-0">
            <i class="bi bi-moon-stars-fill theme-icon fs-5"></i>
        </button>
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($currentUser['full_name']) ?>&background=random"
            class="rounded-circle shadow-sm" width="35" height="35" alt="Profile">
    </div>
</div>

<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
        document.getElementById('sidebarBackdrop').classList.toggle('show');
    }
</script>