<?php
require_once 'config.php';
checkRole(['petugas']);

$success = '';
$error = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $permohonan_id = mysqli_real_escape_string($conn, $_POST['permohonan_id']);
    $action = $_POST['action'];
    $catatan_petugas = mysqli_real_escape_string($conn, $_POST['catatan_petugas']);
    
    if ($action == 'setuju') {
        // Get available bilik
        $bilik_query = "SELECT * FROM bilik WHERE status = 'tersedia' LIMIT 1";
        $bilik_result = mysqli_query($conn, $bilik_query);
        
        if (mysqli_num_rows($bilik_result) > 0) {
            $bilik = mysqli_fetch_assoc($bilik_result);
            $bilik_id = $bilik['id'];
            $no_bilik = $bilik['no_bilik'];
            
            // Update permohonan
            $update_query = "UPDATE permohonan SET 
                            status = 'diluluskan', 
                            petugas_id = '" . $_SESSION['user_id'] . "',
                            bilik_id = '$bilik_id',
                            catatan_petugas = '$catatan_petugas',
                            tarikh_diluluskan = NOW()
                            WHERE id = '$permohonan_id'";
            
            // Update bilik status
            $update_bilik = "UPDATE bilik SET status = 'digunakan' WHERE id = '$bilik_id'";
            
            if (mysqli_query($conn, $update_query) && mysqli_query($conn, $update_bilik)) {
                $success = 'Permohonan berjaya diluluskan! Bilik ' . $no_bilik . ' telah ditetapkan.';
            } else {
                $error = 'Ralat: ' . mysqli_error($conn);
            }
        } else {
            $error = 'Tiada bilik tersedia pada masa ini!';
        }
    } else if ($action == 'tolak') {
        $update_query = "UPDATE permohonan SET 
                        status = 'ditolak', 
                        petugas_id = '" . $_SESSION['user_id'] . "',
                        catatan_petugas = '$catatan_petugas',
                        tarikh_ditolak = NOW()
                        WHERE id = '$permohonan_id'";
        
        if (mysqli_query($conn, $update_query)) {
            $success = 'Permohonan telah ditolak!';
        } else {
            $error = 'Ralat: ' . mysqli_error($conn);
        }
    }
}

// Get all permohonan with filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Query yang DIPERBETUKAN - guna nama_bilik bukan b.nama
$query = "SELECT p.*, pl.nama as nama_pelajar, pl.no_matrik, pl.kelas, pl.no_telefon,
                 u.nama as nama_biro, b.no_bilik, b.nama_bilik
          FROM permohonan p 
          JOIN pelajar pl ON p.pelajar_id = pl.id 
          JOIN users u ON p.biro_id = u.id
          LEFT JOIN bilik b ON p.bilik_id = b.id";

$where_clauses = [];
if (!empty($status_filter)) {
    $where_clauses[] = "p.status = '$status_filter'";
}
if (!empty($search_query)) {
    $search = mysqli_real_escape_string($conn, $search_query);
    $where_clauses[] = "(pl.nama LIKE '%$search%' OR pl.no_matrik LIKE '%$search%' OR pl.no_telefon LIKE '%$search%')";
}
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

$query .= " ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

// Count statistics
$total_query = "SELECT 
                SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN status = 'diluluskan' THEN 1 ELSE 0 END) as diluluskan,
                SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
                COUNT(*) as total
                FROM permohonan";
$stats_result = mysqli_query($conn, $total_query);
$stats = mysqli_fetch_assoc($stats_result);

$page_title = 'Kelulusan Permohonan';
include 'header.php';
?>

<div class="container-fluid">
    <!-- Header dengan statistik -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="mb-1"><i class="fas fa-file-check me-2 text-primary"></i>Kelulusan Permohonan</h3>
                            <p class="text-muted mb-0">Urus permohonan kuarantin pelajar</p>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="fas fa-filter me-2"></i>Penapis
                            </button>
                        </div>
                    </div>
                    
                    <!-- Statistik -->
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center">
                                    <h2 class="text-primary mb-0"><?php echo $stats['total']; ?></h2>
                                    <p class="text-muted mb-0">Jumlah Permohonan</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center">
                                    <h2 class="text-warning mb-0"><?php echo $stats['menunggu']; ?></h2>
                                    <p class="text-muted mb-0">Menunggu</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center">
                                    <h2 class="text-success mb-0"><?php echo $stats['diluluskan']; ?></h2>
                                    <p class="text-muted mb-0">Diluluskan</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center">
                                    <h2 class="text-danger mb-0"><?php echo $stats['ditolak']; ?></h2>
                                    <p class="text-muted mb-0">Ditolak</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Table Permohonan -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Senarai Permohonan</h5>
                <small class="text-muted"><?php echo mysqli_num_rows($result); ?> rekod ditemui</small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Pelajar</th>
                            <th>Tarikh Sakit</th>
                            <th>Simptom</th>
                            <th>Status</th>
                            <th>Dihantar Oleh</th>
                            <th>Tarikh Dihantar</th>
                            <th>Bilik</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): 
                                $status_class = [
                                    'menunggu' => 'warning',
                                    'diluluskan' => 'success',
                                    'ditolak' => 'danger'
                                ][$row['status']];
                            ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['nama_pelajar']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['no_matrik']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($row['tarikh_sakit'])); ?><br>
                                        <small><?php echo date('H:i', strtotime($row['masa_sakit'])); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo substr(htmlspecialchars($row['simptom']), 0, 50); ?>...</small>
                                        <?php if (strlen($row['simptom']) > 50): ?>
                                            <button type="button" class="btn btn-sm btn-link" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detailModal<?php echo $row['id']; ?>">
                                                Lihat
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_class; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($row['nama_biro']); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($row['bilik_id']): ?>
                                            <span class="badge bg-info">
                                                <?php echo $row['no_bilik']; ?><br>
                                                <small><?php echo htmlspecialchars($row['nama_bilik']); ?></small>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tiada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detailModal<?php echo $row['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($row['status'] == 'menunggu'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#approveModal<?php echo $row['id']; ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#rejectModal<?php echo $row['id']; ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Detail -->
                                <div class="modal fade" id="detailModal<?php echo $row['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-file-medical me-2"></i>
                                                    Butiran Permohonan #<?php echo $row['id']; ?>
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="text-muted mb-3">Maklumat Pelajar</h6>
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th width="40%">Nama</th>
                                                                <td><?php echo htmlspecialchars($row['nama_pelajar']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>No Matrik</th>
                                                                <td><?php echo htmlspecialchars($row['no_matrik']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Kelas</th>
                                                                <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>No Telefon</th>
                                                                <td><?php echo htmlspecialchars($row['no_telefon']); ?></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="text-muted mb-3">Maklumat Permohonan</h6>
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th width="40%">Status</th>
                                                                <td>
                                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                                        <?php echo ucfirst($row['status']); ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Tarikh Sakit</th>
                                                                <td><?php echo date('d/m/Y', strtotime($row['tarikh_sakit'])); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Masa Sakit</th>
                                                                <td><?php echo date('H:i', strtotime($row['masa_sakit'])); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Dihantar Oleh</th>
                                                                <td><?php echo htmlspecialchars($row['nama_biro']); ?></td>
                                                            </tr>
                                                            <?php if ($row['bilik_id']): ?>
                                                            <tr>
                                                                <th>Bilik Ditetapkan</th>
                                                                <td>
                                                                    <span class="badge bg-info">
                                                                        <?php echo $row['no_bilik']; ?> - 
                                                                        <?php echo htmlspecialchars($row['nama_bilik']); ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <?php endif; ?>
                                                        </table>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-4">
                                                    <h6 class="text-muted mb-3">Simptom & Catatan</h6>
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h6>Simptom:</h6>
                                                            <p><?php echo nl2br(htmlspecialchars($row['simptom'])); ?></p>
                                                            
                                                            <?php if ($row['catatan']): ?>
                                                                <h6 class="mt-3">Catatan Biro:</h6>
                                                                <p><?php echo nl2br(htmlspecialchars($row['catatan'])); ?></p>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($row['catatan_petugas']): ?>
                                                                <h6 class="mt-3">Catatan Petugas:</h6>
                                                                <p><?php echo nl2br(htmlspecialchars($row['catatan_petugas'])); ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($row['dokumen_sokongan'] || $row['gambar_camera']): ?>
                                                <div class="mt-4">
                                                    <h6 class="text-muted mb-3">Lampiran</h6>
                                                    <div class="row">
                                                        <?php if ($row['dokumen_sokongan']): 
                                                            $dokumen_path = 'uploads/dokumen/' . $row['dokumen_sokongan'];
                                                            $file_ext = pathinfo($row['dokumen_sokongan'], PATHINFO_EXTENSION);
                                                        ?>
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-body text-center">
                                                                    <?php if (in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                                                        <i class="fas fa-file-image fa-3x text-success mb-3"></i>
                                                                    <?php elseif (strtolower($file_ext) == 'pdf'): ?>
                                                                        <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                                                    <?php else: ?>
                                                                        <i class="fas fa-file fa-3x text-primary mb-3"></i>
                                                                    <?php endif; ?>
                                                                    <p class="mb-2">Dokumen Sokongan</p>
                                                                    <?php if (file_exists($dokumen_path)): ?>
                                                                        <a href="<?php echo $dokumen_path; ?>" 
                                                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                                                            Lihat Dokumen
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <small class="text-danger">Fail tidak ditemui</small>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($row['gambar_camera']): 
                                                            $gambar_path = 'uploads/camera/' . $row['gambar_camera'];
                                                        ?>
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-body text-center">
                                                                    <i class="fas fa-camera fa-3x text-info mb-3"></i>
                                                                    <p class="mb-2">Gambar Camera</p>
                                                                    <?php if (file_exists($gambar_path)): ?>
                                                                        <a href="<?php echo $gambar_path; ?>" 
                                                                           target="_blank" class="btn btn-sm btn-outline-success">
                                                                            Lihat Gambar
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <small class="text-danger">Fail tidak ditemui</small>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Setuju -->
                                <div class="modal fade" id="approveModal<?php echo $row['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-check me-2"></i>
                                                    Setuju Permohonan
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="">
                                                <div class="modal-body">
                                                    <p>Adakah anda pasti mahu meluluskan permohonan ini?</p>
                                                    <p><strong>Pelajar:</strong> <?php echo htmlspecialchars($row['nama_pelajar']); ?></p>
                                                    
                                                    <div class="mb-3">
                                                        <label for="catatan_setuju_<?php echo $row['id']; ?>" class="form-label">
                                                            Catatan (Pilihan)
                                                        </label>
                                                        <textarea class="form-control" 
                                                                  id="catatan_setuju_<?php echo $row['id']; ?>" 
                                                                  name="catatan_petugas" rows="3" 
                                                                  placeholder="Masukkan catatan..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <input type="hidden" name="permohonan_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="action" value="setuju">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success">Setuju</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Tolak -->
                                <div class="modal fade" id="rejectModal<?php echo $row['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-times me-2"></i>
                                                    Tolak Permohonan
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="">
                                                <div class="modal-body">
                                                    <p>Adakah anda pasti mahu menolak permohonan ini?</p>
                                                    <p><strong>Pelajar:</strong> <?php echo htmlspecialchars($row['nama_pelajar']); ?></p>
                                                    
                                                    <div class="mb-3">
                                                        <label for="catatan_tolak_<?php echo $row['id']; ?>" class="form-label">
                                                            Sebab Penolakan <span class="text-danger">*</span>
                                                        </label>
                                                        <textarea class="form-control" 
                                                                  id="catatan_tolak_<?php echo $row['id']; ?>" 
                                                                  name="catatan_petugas" rows="3" 
                                                                  placeholder="Masukkan sebab penolakan..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <input type="hidden" name="permohonan_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="action" value="tolak">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger">Tolak</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5>Tiada Permohonan Ditemui</h5>
                                    <p class="text-muted">Tidak ada permohonan yang sepadan dengan kriteria carian.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <?php echo date('d/m/Y H:i:s'); ?>
                </small>
                <small class="text-muted">
                    Sistem Kuarantin Pelajar
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-filter me-2"></i>Penapis Permohonan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="menunggu" <?php echo ($status_filter == 'menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                            <option value="diluluskan" <?php echo ($status_filter == 'diluluskan') ? 'selected' : ''; ?>>Diluluskan</option>
                            <option value="ditolak" <?php echo ($status_filter == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Carian</label>
                        <input type="text" class="form-control" name="search" 
                               value="<?php echo htmlspecialchars($search_query); ?>" 
                               placeholder="Nama/No Matrik/No Telefon...">
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="kelulusan_permohonan.php" class="btn btn-secondary">Reset</a>
                    <button type="submit" class="btn btn-primary">Gunakan Penapis</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #e0e0e0;
}

.card-header {
    border-bottom: 2px solid #f8f9fa;
    border-radius: 10px 10px 0 0 !important;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.badge {
    font-size: 0.85em;
    padding: 0.4em 0.8em;
}

.btn-group .btn {
    border-radius: 5px !important;
    margin-right: 3px;
}

.modal-content {
    border-radius: 10px;
    border: none;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
</style>

<?php include 'footer.php'; ?>