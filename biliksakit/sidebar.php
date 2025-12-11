<?php
// Get current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);

// Get pending count for petugas
if ($_SESSION['peranan'] == 'petugas') {
    $menunggu_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM permohonan WHERE status = 'menunggu'");
    $menunggu_count = mysqli_fetch_assoc($menunggu_query)['total'];
} else {
    $menunggu_count = 0;
}
?>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-hospital"></i>
        <h4>Bilik Sakit KVSP1</h4>
    </div>
    
    <div class="sidebar-user">
        <div class="user-name">
            <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($_SESSION['nama']); ?>
        </div>
        <div class="user-role">
            <?php echo $_SESSION['peranan'] == 'biro' ? 'Biro Kesihatan' : 'Petugas Kesihatan'; ?>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <a href="dashboard.php" class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        
        <?php if ($_SESSION['peranan'] == 'biro'): ?>
            <div class="menu-divider">Biro</div>
            <a href="permohonan_baru.php" class="menu-item <?php echo $current_page == 'permohonan_baru.php' ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Permohonan Baru</span>
            </a>
            <a href="senarai_permohonan.php" class="menu-item <?php echo $current_page == 'senarai_permohonan.php' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i>
                <span>Senarai Permohonan</span>
            </a>
            <a href="pelajar.php" class="menu-item <?php echo $current_page == 'pelajar.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i>
                <span>Urus Pelajar</span>
            </a>
        <?php else: ?>
            <div class="menu-divider">Petugas</div>
            <a href="kelulusan_permohonan.php" class="menu-item <?php echo $current_page == 'kelulusan_permohonan.php' ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i>
                <span>Kelulusan Permohonan</span>
                <?php if ($menunggu_count > 0): ?>
                    <span class="badge bg-warning text-dark ms-2"><?php echo $menunggu_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="urus_bilik.php" class="menu-item <?php echo $current_page == 'urus_bilik.php' ? 'active' : ''; ?>">
                <i class="fas fa-door-open"></i>
                <span>Urus Bilik</span>
            </a>
        <?php endif; ?>
        
        <div class="menu-divider">Lain-lain</div>
        <a href="logout.php" class="menu-item btn-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log Keluar</span>
        </a>
    </div>
</div>

