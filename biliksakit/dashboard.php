<?php
require_once 'config.php';
checkLogin();

// Get statistics based on role
if ($_SESSION['peranan'] == 'biro') {
    // Statistics for Biro
    $total_permohonan = mysqli_query($conn, "SELECT COUNT(*) as total FROM permohonan WHERE biro_id = " . $_SESSION['user_id']);
    $total_permohonan = mysqli_fetch_assoc($total_permohonan)['total'];
    
    $menunggu = mysqli_query($conn, "SELECT COUNT(*) as total FROM permohonan WHERE biro_id = " . $_SESSION['user_id'] . " AND status = 'menunggu'");
    $menunggu = mysqli_fetch_assoc($menunggu)['total'];
    
    $diluluskan = mysqli_query($conn, "SELECT COUNT(*) as total FROM permohonan WHERE biro_id = " . $_SESSION['user_id'] . " AND status = 'diluluskan'");
    $diluluskan = mysqli_fetch_assoc($diluluskan)['total'];
    
    $ditolak = mysqli_query($conn, "SELECT COUNT(*) as total FROM permohonan WHERE biro_id = " . $_SESSION['user_id'] . " AND status = 'ditolak'");
    $ditolak = mysqli_fetch_assoc($ditolak)['total'];
} else {
    // Statistics for Petugas
    $total_permohonan = mysqli_query($conn, "SELECT COUNT(*) as total FROM permohonan");
    $total_permohonan = mysqli_fetch_assoc($total_permohonan)['total'];
    
    $menunggu = mysqli_query($conn, "SELECT COUNT(*) as total FROM permohonan WHERE status = 'menunggu'");
    $menunggu = mysqli_fetch_assoc($menunggu)['total'];
    
    $diluluskan = mysqli_query($conn, "SELECT COUNT(*) as total FROM permohonan WHERE status = 'diluluskan'");
    $diluluskan = mysqli_fetch_assoc($diluluskan)['total'];
    
    $ditolak = mysqli_query($conn, "SELECT COUNT(*) as total FROM permohonan WHERE status = 'ditolak'");
    $ditolak = mysqli_fetch_assoc($ditolak)['total'];
}

$page_title = 'Dashboard';
include 'header.php';
?>

<!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card total">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Jumlah Permohonan</h6>
                                <h3 class="mb-0"><?php echo $total_permohonan; ?></h3>
                            </div>
                            <i class="fas fa-clipboard-list stat-icon" style="color: #3498db;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card menunggu">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Menunggu</h6>
                                <h3 class="mb-0 text-warning"><?php echo $menunggu; ?></h3>
                            </div>
                            <i class="fas fa-clock stat-icon text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card diluluskan">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Diluluskan</h6>
                                <h3 class="mb-0 text-success"><?php echo $diluluskan; ?></h3>
                            </div>
                            <i class="fas fa-check-circle stat-icon text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card ditolak">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Ditolak</h6>
                                <h3 class="mb-0 text-danger"><?php echo $ditolak; ?></h3>
                            </div>
                            <i class="fas fa-times-circle stat-icon text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Tindakan Pantas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php if ($_SESSION['peranan'] == 'biro'): ?>
                                <div class="col-md-4">
                                    <a href="permohonan_baru.php" class="btn btn-primary w-100 py-3">
                                        <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                                        Buat Permohonan Baru
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="senarai_permohonan.php" class="btn btn-secondary w-100 py-3">
                                        <i class="fas fa-list fa-2x d-block mb-2"></i>
                                        Lihat Semua Permohonan
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="pelajar.php" class="btn btn-success w-100 py-3">
                                        <i class="fas fa-user-graduate fa-2x d-block mb-2"></i>
                                        Urus Pelajar
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="col-md-6">
                                    <a href="kelulusan_permohonan.php" class="btn btn-warning w-100 py-3">
                                        <i class="fas fa-check-circle fa-2x d-block mb-2"></i>
                                        Semak Permohonan (<?php echo $menunggu; ?>)
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="urus_bilik.php" class="btn btn-secondary w-100 py-3">
                                        <i class="fas fa-door-open fa-2x d-block mb-2"></i>
                                        Urus Bilik Isolasi
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php include 'footer.php'; ?>
