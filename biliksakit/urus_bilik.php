<?php
require_once 'config.php';
checkRole(['petugas']);

$success = '';
$error = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        // Create
        $no_bilik = mysqli_real_escape_string($conn, $_POST['no_bilik']);
        $nama_bilik = mysqli_real_escape_string($conn, $_POST['nama_bilik']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        // Check if no_bilik already exists
        $check_query = "SELECT * FROM bilik WHERE no_bilik = '$no_bilik'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'No. Bilik sudah wujud!';
        } else {
            $query = "INSERT INTO bilik (no_bilik, nama_bilik, status) VALUES ('$no_bilik', '$nama_bilik', '$status')";
            if (mysqli_query($conn, $query)) {
                $success = 'Bilik berjaya ditambah!';
                header("Location: urus_bilik.php?success=1");
                exit();
            } else {
                $error = 'Ralat: ' . mysqli_error($conn);
            }
        }
    } elseif (isset($_POST['edit'])) {
        // Update
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $no_bilik = mysqli_real_escape_string($conn, $_POST['no_bilik']);
        $nama_bilik = mysqli_real_escape_string($conn, $_POST['nama_bilik']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        // Check if no_bilik already exists (except current)
        $check_query = "SELECT * FROM bilik WHERE no_bilik = '$no_bilik' AND id != '$id'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'No. Bilik sudah wujud!';
        } else {
            $query = "UPDATE bilik SET no_bilik = '$no_bilik', nama_bilik = '$nama_bilik', status = '$status' WHERE id = '$id'";
            if (mysqli_query($conn, $query)) {
                $success = 'Bilik berjaya dikemaskini!';
                header("Location: urus_bilik.php?success=1");
                exit();
            } else {
                $error = 'Ralat: ' . mysqli_error($conn);
            }
        }
    } elseif (isset($_POST['delete'])) {
        // Delete
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        
        // Check if bilik is being used
        $check_query = "SELECT * FROM permohonan WHERE bilik_id = '$id' AND status = 'diluluskan'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Bilik sedang digunakan! Tidak boleh dipadam.';
        } else {
            $query = "DELETE FROM bilik WHERE id = '$id'";
            if (mysqli_query($conn, $query)) {
                $success = 'Bilik berjaya dipadam!';
                header("Location: urus_bilik.php?success=1");
                exit();
            } else {
                $error = 'Ralat: ' . mysqli_error($conn);
            }
        }
    }
}

// Get success message from URL
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = 'Operasi berjaya dilakukan!';
}

// Get bilik for edit
$edit_bilik = null;
if (isset($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $edit_query = "SELECT * FROM bilik WHERE id = '$edit_id'";
    $edit_result = mysqli_query($conn, $edit_query);
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_bilik = mysqli_fetch_assoc($edit_result);
    }
}

// Get all bilik
$query = "SELECT b.*, COUNT(p.id) as jumlah_penggunaan 
          FROM bilik b 
          LEFT JOIN permohonan p ON b.id = p.bilik_id AND p.status = 'diluluskan'
          GROUP BY b.id
          ORDER BY b.no_bilik";
$result = mysqli_query($conn, $query);

$page_title = 'Urus Bilik';
include 'header.php';
?>

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

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-door-open me-2"></i>Senarai Bilik Isolasi</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahBilikModal">
            <i class="fas fa-plus me-2"></i>Tambah Bilik
        </button>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $status_class = 'status-' . $row['status'];
                $status_badge = [
                    'tersedia' => ['bg-success', 'Tersedia'],
                    'digunakan' => ['bg-danger', 'Digunakan'],
                    'pembersihan' => ['bg-warning text-dark', 'Pembersihan']
                ];
                $badge = $status_badge[$row['status']];
            ?>
                <div class="col-md-4">
                    <div class="card bilik-card <?php echo $status_class; ?>" style="border-left: 4px solid <?php echo $row['status'] == 'tersedia' ? '#28a745' : ($row['status'] == 'digunakan' ? '#dc3545' : '#ffc107'); ?>;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">
                                        <i class="fas fa-door-open me-2"></i>
                                        <?php echo htmlspecialchars($row['no_bilik']); ?>
                                    </h5>
                                    <p class="text-muted mb-0">
                                        <?php echo htmlspecialchars($row['nama_bilik']); ?>
                                    </p>
                                </div>
                                <span class="badge <?php echo $badge[0]; ?>">
                                    <?php echo $badge[1]; ?>
                                </span>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-2">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Status:</strong> 
                                <?php echo ucfirst($row['status']); ?>
                            </div>
                            
                            <div class="mb-3">
                                <i class="fas fa-chart-line me-2"></i>
                                <strong>Jumlah Penggunaan:</strong> 
                                <?php echo $row['jumlah_penggunaan']; ?>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex">
                                <button type="button" class="btn btn-sm btn-primary flex-fill" 
                                        onclick="editBilik(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['no_bilik'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['nama_bilik'], ENT_QUOTES); ?>', '<?php echo $row['status']; ?>')">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger flex-fill" 
                                        onclick="deleteBilik(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['no_bilik'], ENT_QUOTES); ?>')">
                                    <i class="fas fa-trash me-1"></i>Padam
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <?php if (mysqli_num_rows($result) == 0): ?>
                <div class="col-12">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-door-open fa-3x d-block mb-3"></i>
                        <p>Tiada bilik dijumpai. Sila tambah bilik baru.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Bilik -->
<div class="modal fade" id="tambahBilikModal" tabindex="-1" aria-labelledby="tambahBilikModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;">
                <h5 class="modal-title" id="tambahBilikModalLabel">
                    <i class="fas fa-plus me-2"></i>Tambah Bilik Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="no_bilik" class="form-label">No. Bilik <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="no_bilik" name="no_bilik" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nama_bilik" class="form-label">Nama Bilik</label>
                        <input type="text" class="form-control" id="nama_bilik" name="nama_bilik">
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="digunakan">Digunakan</option>
                            <option value="pembersihan">Pembersihan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="submit" name="add" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Bilik -->
<div class="modal fade" id="editBilikModal" tabindex="-1" aria-labelledby="editBilikModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;">
                <h5 class="modal-title" id="editBilikModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Bilik
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_no_bilik" class="form-label">No. Bilik <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_no_bilik" name="no_bilik" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_nama_bilik" class="form-label">Nama Bilik</label>
                        <input type="text" class="form-control" id="edit_nama_bilik" name="nama_bilik">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="digunakan">Digunakan</option>
                            <option value="pembersihan">Pembersihan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="submit" name="edit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Kemaskini
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete Confirmation -->
<div class="modal fade" id="deleteBilikModal" tabindex="-1" aria-labelledby="deleteBilikModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteBilikModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Padam Bilik
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="delete_id" name="id">
                <div class="modal-body">
                    <p>Adakah anda pasti ingin memadam bilik <strong id="delete_no_bilik"></strong>?</p>
                    <p class="text-danger"><small>Tindakan ini tidak boleh dibatalkan.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="submit" name="delete" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Padam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
// Reset form when modal is closed
document.getElementById('tambahBilikModal').addEventListener('hidden.bs.modal', function () {
    document.querySelector('#tambahBilikModal form').reset();
});

function editBilik(id, no_bilik, nama_bilik, status) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_no_bilik').value = no_bilik;
    document.getElementById('edit_nama_bilik').value = nama_bilik;
    document.getElementById('edit_status').value = status;
    
    var editModal = new bootstrap.Modal(document.getElementById('editBilikModal'));
    editModal.show();
}

function deleteBilik(id, no_bilik) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_no_bilik').textContent = no_bilik;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteBilikModal'));
    deleteModal.show();
}
</script>
